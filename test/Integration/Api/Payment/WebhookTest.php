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

namespace KST\Integration\Api\Payment;

use DateTimeImmutable;
use Keestash\Api\Payment\Webhook;
use Keestash\Core\DTO\Payment\Log;
use Keestash\Core\DTO\Payment\Payment;
use KSP\Api\IResponse;
use KSP\Core\DTO\Payment\ICheckout;
use KSP\Core\DTO\Payment\IPayment;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\User\IUserStateService;
use KST\Integration\TestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Mollie\Api\Webhooks\SignatureValidator;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

class WebhookTest extends TestCase {

    public const string PLAN     = 'personal';
    public const float  PRICE    = 4.99;
    public const string CURRENCY = 'EUR';

    /**
     * Hand written payment service (no mocking library) that returns a
     * pre-canned payment for a given id and records the arguments of any
     * startRecurringBilling call so a test can assert the subscription is
     * created exactly once on activation.
     */
    private function paymentService(IPayment $payment): RecordingPaymentService {
        return new RecordingPaymentService($payment);
    }

    /**
     * Config stub exposing a single known plan so the webhook can verify the
     * paid amount against it, and an optional webhook signing secret.
     */
    private function configService(string $webhookSecret = ''): IConfigService {
        return new class($webhookSecret) implements IConfigService {
            public function __construct(private readonly string $webhookSecret) {
            }

            public function getValue(string $key, $default = null) {
                if ('mollie_plans' === $key) {
                    return [
                        WebhookTest::PLAN => [
                            'price'    => WebhookTest::PRICE,
                            'currency' => WebhookTest::CURRENCY,
                            'interval' => '1 month'
                        ]
                    ];
                }
                if ('mollie_webhook_secret' === $key) {
                    return $this->webhookSecret;
                }
                return $default;
            }

            public function getAll(): array {
                return [];
            }
        };
    }

    private function webhook(IPayment $payment, ?IPaymentService $paymentService = null, string $webhookSecret = ''): Webhook {
        return new Webhook(
            $paymentService ?? $this->paymentService($payment),
            $this->getService(IPaymentLogRepository::class),
            $this->getService(IUserRepository::class),
            $this->getService(IUserStateService::class),
            $this->configService($webhookSecret),
            new NullLogger()
        );
    }

    private function request(?string $paymentId): ServerRequest {
        return (new ServerRequest())
            ->withMethod('POST')
            ->withParsedBody(null === $paymentId ? [] : ['id' => $paymentId]);
    }

    /**
     * A request whose raw body carries the payment id, optionally signed with
     * the given secret in the X-Mollie-Signature header (sha256=... form).
     */
    private function signedRequest(string $paymentId, ?string $signingSecret, bool $validSignature = true): ServerRequest {
        $rawBody = 'id=' . $paymentId;
        $stream  = new Stream('php://temp', 'wb+');
        $stream->write($rawBody);
        $stream->rewind();

        $request = (new ServerRequest())
            ->withMethod('POST')
            ->withParsedBody(['id' => $paymentId])
            ->withBody($stream);

        if (null !== $signingSecret) {
            $signature = $validSignature
                ? SignatureValidator::createSignature($rawBody, $signingSecret)
                : 'deadbeef';
            $request = $request->withHeader(SignatureValidator::SIGNATURE_HEADER, 'sha256=' . $signature);
        }

        return $request;
    }

    /**
     * Writes the registration log row the Add handler would have written,
     * correlating the session to the user and the chosen plan.
     */
    private function seedRegistration(string $session, string $email, string $plan = self::PLAN, string $customerId = 'cst_test'): void {
        $this->getService(IPaymentLogRepository::class)->insert(
            new Log(
                key: $session,
                log: [
                    'plan'       => $plan,
                    'customerId' => $customerId,
                    'user'       => ['id' => 'irrelevant', 'email' => $email]
                ],
                createTs: new DateTimeImmutable()
            )
        );
    }

    private function paidPayment(string $session, string $email, float $amount = self::PRICE, string $currency = self::CURRENCY): Payment {
        return new Payment(
            id: 'tr_' . $session,
            status: Type::PAID,
            metadata: ['email' => $email, 'session' => $session, 'plan' => self::PLAN],
            payload: ['status' => Type::PAID->value, 'amount' => ['value' => number_format($amount, 2, '.', ''), 'currency' => $currency]],
            amount: $amount,
            currency: $currency
        );
    }

    public function testPaidWebhookActivatesUserAndLogs(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $user  = $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);
        $this->assertTrue($user->isLocked());

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = $this->paidPayment($session, $email);

        $service = $this->paymentService($payment);
        $response = $this->webhook($payment, $service)->handle($this->request($payment->getId()));
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertFalse($reloaded->isLocked());

        // Activation must also start recurring billing exactly once, against the
        // customer recorded at registration - a first payment alone never charges
        // again.
        $this->assertCount(1, $service->recurringBillingCalls);
        $this->assertSame('cst_test', $service->recurringBillingCalls[0]['customerId']);
        $this->assertSame(self::PLAN, $service->recurringBillingCalls[0]['planName']);
        $this->assertSame($email, $service->recurringBillingCalls[0]['email']);
        $this->assertSame($session, $service->recurringBillingCalls[0]['sessionId']);

        // The subscription marker is persisted so a later webhook delivery does
        // not create a second subscription.
        $marker = $this->getService(IPaymentLogRepository::class)->get('subscription:' . $session);
        $this->assertSame('sub_' . $session, $marker->getLog()['subscriptionId']);
    }

    public function testRecurringBillingStartedOnlyOncePerSession(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = $this->paidPayment($session, $email);

        // First delivery: activates and subscribes.
        $service = $this->paymentService($payment);
        $this->webhook($payment, $service)->handle($this->request($payment->getId()));
        $this->assertCount(1, $service->recurringBillingCalls);

        // A subsequent paid delivery for the same session (e.g. Mollie's first
        // subscription charge) must not create a second subscription. The user
        // is already active and the subscription marker exists.
        $service2 = $this->paymentService(
            new Payment(
                id: 'tr_recurring_' . $session,
                status: Type::PAID,
                metadata: ['email' => $email, 'session' => $session, 'plan' => self::PLAN, 'customerId' => 'cst_test'],
                payload: ['status' => Type::PAID->value, 'amount' => ['value' => number_format(self::PRICE, 2, '.', ''), 'currency' => self::CURRENCY]],
                amount: self::PRICE,
                currency: self::CURRENCY
            )
        );
        $this->webhook($payment, $service2)->handle($this->request('tr_recurring_' . $session));
        $this->assertCount(0, $service2->recurringBillingCalls);
    }

    public function testFailedWebhookKeepsUserLocked(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = new Payment(
            id: 'tr_' . $session,
            status: Type::FAILED,
            metadata: ['email' => $email, 'session' => $session, 'plan' => self::PLAN],
            payload: ['status' => Type::FAILED->value],
            amount: self::PRICE,
            currency: self::CURRENCY
        );

        $response = $this->webhook($payment)->handle($this->request($payment->getId()));
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertTrue($reloaded->isLocked());
    }

    public function testMissingIdReturnsOk(): void {
        $payment = new Payment('tr_x', Type::PAID, [], []);
        $response = $this->webhook($payment)->handle($this->request(null));
        $this->assertSame(IResponse::OK, $response->getStatusCode());
    }

    public function testValidSignatureIsAcceptedAndActivates(): void {
        $secret = 'whsec_' . Uuid::uuid4()->toString();
        $email  = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = $this->paidPayment($session, $email);

        $request  = $this->signedRequest($payment->getId(), $secret);
        $response = $this->webhook($payment, null, $secret)->handle($request);
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertFalse($reloaded->isLocked());
    }

    public function testInvalidSignatureIsRejectedAndDoesNotActivate(): void {
        $secret = 'whsec_' . Uuid::uuid4()->toString();
        $email  = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = $this->paidPayment($session, $email);

        // Signature header present but wrong: the request must be rejected with a
        // 400 and must not activate the account.
        $request  = $this->signedRequest($payment->getId(), $secret, validSignature: false);
        $response = $this->webhook($payment, null, $secret)->handle($request);
        $this->assertSame(IResponse::BAD_REQUEST, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertTrue($reloaded->isLocked());
    }

    public function testUnsignedRequestStillProcessedWhenSecretConfigured(): void {
        // A secret is configured but the request carries no signature header
        // (legacy webhook). It must fall through to the re-fetch flow and
        // activate, so enabling the secret never breaks legacy delivery.
        $secret = 'whsec_' . Uuid::uuid4()->toString();
        $email  = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = $this->paidPayment($session, $email);

        $response = $this->webhook($payment, null, $secret)->handle($this->request($payment->getId()));
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertFalse($reloaded->isLocked());
    }

    public function testPaidWebhookWithoutRegistrationDoesNotActivate(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        // No seedRegistration: an attacker points the webhook at a paid payment
        // whose session this instance never created.
        $session = Uuid::uuid4()->toString();
        $payment = $this->paidPayment($session, $email);

        $response = $this->webhook($payment)->handle($this->request($payment->getId()));
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertTrue($reloaded->isLocked());
    }

    public function testPaidWebhookWithWrongEmailDoesNotActivate(): void {
        $victimEmail   = Uuid::uuid4() . '@keestash.com';
        $attackerEmail = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $victimEmail);

        // Registration belongs to the attacker's own session/email, but the
        // payment metadata claims the victim's email.
        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $attackerEmail);
        $payment = $this->paidPayment($session, $victimEmail);

        $response = $this->webhook($payment)->handle($this->request($payment->getId()));
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($victimEmail);
        $this->assertTrue($reloaded->isLocked());
    }

    public function testPaidWebhookWithUnderpaymentDoesNotActivate(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        // Paid, but for 1 cent instead of the plan price.
        $payment = $this->paidPayment($session, $email, 0.01);

        $response = $this->webhook($payment)->handle($this->request($payment->getId()));
        $this->assertSame(IResponse::OK, $response->getStatusCode());

        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertTrue($reloaded->isLocked());
    }

    public function testReplayedWebhookDoesNotReactivateRelockedUser(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $this->createUser(Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), true, $email);

        $session = Uuid::uuid4()->toString();
        $this->seedRegistration($session, $email);
        $payment = $this->paidPayment($session, $email);

        // First (legitimate) delivery activates.
        $this->webhook($payment)->handle($this->request($payment->getId()));
        $reloaded = $this->getService(IUserRepository::class)->getUserByEmail($email);
        $this->assertFalse($reloaded->isLocked());

        // An operator re-locks the account.
        $this->getService(IUserStateService::class)->forceLock($reloaded);
        $this->assertTrue(
            $this->getService(IUserRepository::class)->getUserByEmail($email)->isLocked()
        );

        // A replayed "paid" webhook must NOT re-activate the account.
        $this->webhook($payment)->handle($this->request($payment->getId()));
        $this->assertTrue(
            $this->getService(IUserRepository::class)->getUserByEmail($email)->isLocked()
        );
    }

}

/**
 * Records startRecurringBilling calls so tests can assert the subscription is
 * created exactly once. Kept as a named class (rather than an anonymous one) so
 * the recording property is statically typed.
 */
final class RecordingPaymentService implements IPaymentService {

    /** @var list<array{customerId: string, planName: string, email: string, sessionId: string}> */
    public array $recurringBillingCalls = [];

    public function __construct(private readonly IPayment $payment) {
    }

    public function createSubscription(string $planName, string $email, string $sessionId, string $lang): ICheckout {
        throw new \RuntimeException('not used');
    }

    public function getPayment(string $paymentId): IPayment {
        return $this->payment;
    }

    public function startRecurringBilling(string $customerId, string $planName, string $email, string $sessionId): string {
        $this->recurringBillingCalls[] = [
            'customerId' => $customerId,
            'planName'   => $planName,
            'email'      => $email,
            'sessionId'  => $sessionId
        ];
        return 'sub_' . $sessionId;
    }

    public function cancelSubscriptionImmediately(string $customerId, string $subscriptionId): void {
    }

    public function cancelSubscriptionToTheEndOfThePeriod(string $customerId, string $subscriptionId): void {
    }

}
