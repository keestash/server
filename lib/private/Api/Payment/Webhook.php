<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2026> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Keestash\Api\Payment;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Payment\Log;
use Keestash\Core\DTO\User\NullUser;
use Keestash\Core\DTO\User\UserStateName;
use KSP\Api\IResponse;
use KSP\Core\DTO\Payment\ILog;
use KSP\Core\DTO\Payment\IPayment;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\User\IUserStateService;
use Mollie\Api\Exceptions\InvalidSignatureException;
use Mollie\Api\Webhooks\SignatureValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Handles Mollie payment webhooks.
 *
 * The classic ("legacy") payment webhook Mollie sends carries only the payment
 * id and is NOT signed, so the request body is treated as untrusted. The trusted
 * state is obtained by re-fetching the payment from the Mollie API
 * (IPaymentService::getPayment).
 *
 * Mollie's newer signed ("next-gen") webhooks additionally carry an
 * X-Mollie-Signature (HMAC-SHA256) header. When such a header is present AND a
 * mollie_webhook_secret is configured, the raw body is verified before it is
 * trusted and a tampered/forged signature is rejected. A request without a
 * signature header is treated as a legacy webhook and continues through the
 * re-fetch flow below, so enabling the secret never breaks legacy delivery.
 *
 * Because the endpoint is public and unauthenticated, fetching the payment is
 * NOT by itself sufficient to authorise activation: an attacker can post any
 * payment id. Activation therefore additionally requires that
 *
 *   1. the payment carries a `session` this instance itself created at
 *      registration (a matching payment_log row must exist), and the email in
 *      the payment metadata matches the email recorded for that session;
 *   2. the paid amount and currency match the plan the registration was created
 *      for; and
 *   3. the payment has not already activated an account (idempotent replay
 *      protection), since Mollie legitimately calls the webhook several times.
 *
 * The handler always responds with 200 OK so Mollie does not retry indefinitely
 * on a permanent error; failures are logged for investigation.
 */
final readonly class Webhook implements RequestHandlerInterface {

    private const string ACTIVATION_KEY_PREFIX   = 'activation:';
    private const string WEBHOOK_KEY_PREFIX      = 'webhook:';
    private const string SUBSCRIPTION_KEY_PREFIX = 'subscription:';

    public function __construct(
        private IPaymentService       $paymentService,
        private IPaymentLogRepository $paymentLogRepository,
        private IUserRepository       $userRepository,
        private IUserStateService     $userStateService,
        private IConfigService        $configService,
        private LoggerInterface       $logger
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        if (false === $this->signatureAccepted($request)) {
            // A signature header was present but did not verify: reject rather
            // than acknowledge, so a forged or tampered request is not treated
            // as a genuine (permanent-error) Mollie delivery.
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        try {
            $body      = (array) $request->getParsedBody();
            $paymentId = $body['id'] ?? null;

            if (false === is_string($paymentId) || '' === $paymentId) {
                $this->logger->warning('mollie webhook without valid id', ['body' => $body]);
                return $this->ok();
            }

            $payment  = $this->paymentService->getPayment($paymentId);
            $metadata = $payment->getMetadata();

            // Webhook events are logged under a dedicated key namespace so they
            // never collide with the registration row (keyed by the raw session
            // id), which the activation checks rely on being able to read back.
            $this->paymentLogRepository->insert(
                new Log(
                    key: self::WEBHOOK_KEY_PREFIX . $payment->getId(),
                    log: $payment->getPayload() + $metadata + ['status' => $payment->getStatus()->value],
                    createTs: new DateTimeImmutable()
                )
            );

            $this->logger->info('mollie webhook processed', [
                'paymentId' => $payment->getId(),
                'status'    => $payment->getStatus()->value
            ]);

            if (Type::PAID === $payment->getStatus()) {
                $this->activateUser($payment);
            }
        } catch (Throwable $e) {
            // Always acknowledge with 200 so Mollie does not retry on a
            // permanent error; the failure is logged for investigation.
            $this->logger->error('mollie webhook processing failed', ['exception' => $e]);
        }

        return $this->ok();
    }

    private function activateUser(IPayment $payment): void {
        $metadata = $payment->getMetadata();

        $session = $metadata['session'] ?? null;
        if (false === is_string($session) || '' === $session) {
            $this->logger->warning('paid mollie webhook without session metadata', [
                'paymentId' => $payment->getId()
            ]);
            return;
        }

        $email = $metadata['email'] ?? null;
        if (false === is_string($email) || '' === $email) {
            $this->logger->warning('paid mollie webhook without email metadata', [
                'paymentId' => $payment->getId()
            ]);
            return;
        }

        // (3) Idempotent replay protection: refuse to act twice on the same
        // payment. Mollie calls the webhook repeatedly and the endpoint is
        // public, so a re-delivered or replayed "paid" notification must be a
        // no-op once an activation has been recorded for this payment id.
        if (true === $this->alreadyActivated($payment->getId())) {
            $this->logger->info('paid mollie webhook already processed for payment', [
                'paymentId' => $payment->getId()
            ]);
            return;
        }

        // (1) Session linkage: the payment must correspond to a registration
        // this instance created. The registration wrote a payment_log row keyed
        // by the session id containing the user email; require it and require
        // the email to match, so an attacker cannot activate an arbitrary
        // account by pointing the webhook at an unrelated paid payment.
        $registration = $this->getRegistrationLog($session);
        if (null === $registration) {
            $this->logger->warning('paid mollie webhook for unknown session', [
                'paymentId' => $payment->getId(),
                'session'   => $session
            ]);
            return;
        }

        $registeredEmail = $this->extractRegisteredEmail($registration);
        if ($registeredEmail !== $email) {
            $this->logger->warning('paid mollie webhook email does not match registration', [
                'paymentId' => $payment->getId(),
                'session'   => $session
            ]);
            return;
        }

        // (2) Amount verification: the charged amount and currency must match
        // the plan the registration was created for, so a self-initiated
        // underpayment cannot unlock a full account.
        if (false === $this->amountMatchesPlan($registration, $payment)) {
            $this->logger->warning('paid mollie webhook amount does not match plan', [
                'paymentId' => $payment->getId(),
                'session'   => $session,
                'amount'    => $payment->getAmount(),
                'currency'  => $payment->getCurrency()
            ]);
            return;
        }

        try {
            $user = $this->userRepository->getUserByEmail($email);
        } catch (Throwable $e) {
            $this->logger->error('could not resolve user for paid webhook', [
                'exception' => $e->getMessage(),
                'session'   => $session
            ]);
            return;
        }

        if ($user instanceof NullUser) {
            $this->logger->warning('paid mollie webhook for unknown user', [
                'paymentId' => $payment->getId(),
                'session'   => $session
            ]);
            return;
        }

        if (false === $user->isLocked()) {
            $this->logger->info('user already active for paid webhook', ['userId' => $user->getId()]);
            $this->recordActivation($payment->getId(), $session);
            return;
        }

        try {
            $this->userStateService->clearCarefully($user, UserStateName::LOCK);
            $this->recordActivation($payment->getId(), $session);
            $this->logger->info('user activated after successful payment', ['userId' => $user->getId()]);
        } catch (Throwable $e) {
            $this->logger->error('could not activate user after payment', [
                'exception' => $e->getMessage(),
                'userId'    => $user->getId()
            ]);
            return;
        }

        // The first payment only established a mandate; without a subscription
        // Mollie never charges again. Now that the first payment is confirmed
        // paid, create the subscription that drives the recurring charges. This
        // is gated on the session (not the payment id) so it happens exactly
        // once per registration, even though every subscription payment Mollie
        // subsequently makes also calls this webhook.
        $this->startRecurringBilling($registration, $payment, $session);
    }

    private function startRecurringBilling(ILog $registration, IPayment $payment, string $session): void {
        if (true === $this->alreadySubscribed($session)) {
            return;
        }

        $customerId = $this->extractCustomerId($registration, $payment);
        if (null === $customerId) {
            $this->logger->error('cannot start recurring billing without a customer id', [
                'paymentId' => $payment->getId(),
                'session'   => $session
            ]);
            return;
        }

        $plan  = $registration->getLog()['plan'] ?? null;
        $email = $payment->getMetadata()['email'] ?? null;
        if (false === is_string($plan) || false === is_string($email)) {
            $this->logger->error('cannot start recurring billing without plan and email', [
                'paymentId' => $payment->getId(),
                'session'   => $session
            ]);
            return;
        }

        try {
            $subscriptionId = $this->paymentService->startRecurringBilling(
                $customerId,
                $plan,
                $email,
                $session
            );
            $this->recordSubscription($session, $customerId, $subscriptionId);
            $this->logger->info('recurring billing started', [
                'session'        => $session,
                'subscriptionId' => $subscriptionId
            ]);
        } catch (Throwable $e) {
            // The account is already activated; failing to start recurring
            // billing must not undo that. It is logged so a subscription can be
            // created out of band, and the missing subscription marker means a
            // later webhook delivery will retry.
            $this->logger->error('could not start recurring billing', [
                'exception' => $e->getMessage(),
                'session'   => $session
            ]);
        }
    }

    /**
     * The Mollie customer id was recorded on the registration row at checkout
     * and is also surfaced into the fetched payment metadata; prefer whichever
     * is present.
     */
    private function extractCustomerId(ILog $registration, IPayment $payment): ?string {
        $fromRegistration = $registration->getLog()['customerId'] ?? null;
        if (is_string($fromRegistration) && '' !== $fromRegistration) {
            return $fromRegistration;
        }
        $fromPayment = $payment->getMetadata()['customerId'] ?? null;
        return is_string($fromPayment) && '' !== $fromPayment ? $fromPayment : null;
    }

    private function getRegistrationLog(string $session): ?ILog {
        try {
            $log = $this->paymentLogRepository->get($session);
        } catch (Throwable) {
            return null;
        }

        // The registration log records the user under a 'user' key. Webhook
        // logs written above do not, so this also distinguishes the two.
        $user = $log->getLog()['user'] ?? null;
        return is_array($user) ? $log : null;
    }

    private function extractRegisteredEmail(ILog $registration): ?string {
        $user = $registration->getLog()['user'] ?? null;
        if (false === is_array($user)) {
            return null;
        }
        $email = $user['email'] ?? null;
        return is_string($email) && '' !== $email ? $email : null;
    }

    private function amountMatchesPlan(ILog $registration, IPayment $payment): bool {
        $plan = $registration->getLog()['plan'] ?? null;
        if (false === is_string($plan) || '' === $plan) {
            return false;
        }

        $plans = (array) $this->configService->getValue('mollie_plans', []);
        $config = $plans[$plan] ?? null;
        if (false === is_array($config)) {
            return false;
        }

        $expectedPrice    = isset($config['price']) && is_numeric($config['price'])
            ? (float) $config['price']
            : null;
        $expectedCurrency = isset($config['currency']) && is_string($config['currency'])
            ? $config['currency']
            : null;

        if (null === $expectedPrice || null === $expectedCurrency) {
            return false;
        }

        $paidAmount   = $payment->getAmount();
        $paidCurrency = $payment->getCurrency();
        if (null === $paidAmount || null === $paidCurrency) {
            return false;
        }

        // Compare money in integer minor units to avoid float rounding, and
        // require an exact match of amount and currency.
        return $paidCurrency === $expectedCurrency
            && (int) round($paidAmount * 100) === (int) round($expectedPrice * 100);
    }

    private function alreadyActivated(string $paymentId): bool {
        try {
            $this->paymentLogRepository->get(self::ACTIVATION_KEY_PREFIX . $paymentId);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function alreadySubscribed(string $session): bool {
        try {
            $this->paymentLogRepository->get(self::SUBSCRIPTION_KEY_PREFIX . $session);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function recordSubscription(string $session, string $customerId, string $subscriptionId): void {
        try {
            $this->paymentLogRepository->insert(
                new Log(
                    key: self::SUBSCRIPTION_KEY_PREFIX . $session,
                    log: [
                        'session'        => $session,
                        'customerId'     => $customerId,
                        'subscriptionId' => $subscriptionId
                    ],
                    createTs: new DateTimeImmutable()
                )
            );
        } catch (Throwable $e) {
            // Best effort: the subscription exists at Mollie either way. If the
            // marker cannot be written a later webhook may attempt to create a
            // second subscription; that is logged here so it can be reconciled.
            $this->logger->error('could not record subscription marker', [
                'exception'      => $e->getMessage(),
                'session'        => $session,
                'subscriptionId' => $subscriptionId
            ]);
        }
    }

    private function recordActivation(string $paymentId, string $session): void {
        try {
            $this->paymentLogRepository->insert(
                new Log(
                    key: self::ACTIVATION_KEY_PREFIX . $paymentId,
                    log: ['session' => $session, 'activated' => true],
                    createTs: new DateTimeImmutable()
                )
            );
        } catch (Throwable $e) {
            // Best effort: if the marker cannot be written the account is still
            // activated; a subsequent duplicate webhook would find the user
            // already unlocked and no-op via isLocked().
            $this->logger->error('could not record activation marker', [
                'exception' => $e->getMessage(),
                'paymentId' => $paymentId
            ]);
        }
    }

    /**
     * Verifies a Mollie webhook signature when applicable.
     *
     * Returns true (proceed) when signature verification does not apply — no
     * secret configured, or no X-Mollie-Signature header (legacy webhook) — and
     * when a present signature verifies. Returns false only when a signature is
     * present but does not match the configured secret.
     */
    private function signatureAccepted(ServerRequestInterface $request): bool {
        $secret = (string) $this->configService->getValue('mollie_webhook_secret', '');
        if ('' === $secret) {
            return true;
        }

        $signatures = $request->getHeader(SignatureValidator::SIGNATURE_HEADER);
        if ([] === $signatures) {
            // Legacy webhook without a signature; verification does not apply and
            // the re-fetch flow authenticates it instead.
            return true;
        }

        // The signature is computed over the raw request body, so rewind and read
        // the stream directly rather than using the parsed body.
        $stream = $request->getBody();
        $stream->rewind();
        $rawBody = $stream->getContents();

        try {
            return (new SignatureValidator($secret))->validatePayload($rawBody, $signatures);
        } catch (InvalidSignatureException $e) {
            $this->logger->warning('mollie webhook signature verification failed', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    private function ok(): ResponseInterface {
        return new JsonResponse([], IResponse::OK);
    }

}
