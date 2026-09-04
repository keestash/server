<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Service\Payment;

use Keestash\Core\DTO\Payment\Checkout;
use Keestash\Core\DTO\Payment\Payment;
use Keestash\Exception\Payment\PaymentException;
use KSP\Core\DTO\Payment\ICheckout;
use KSP\Core\DTO\Payment\IPayment;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Payment\IPaymentService;
use Mollie\Api\MollieApiClient;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class DefaultPaymentService implements IPaymentService {

    public function __construct(
        private MollieApiClient $mollieApiClient,
        private IConfigService  $configService,
        private LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function createSubscription(
        string $planName,
        string $email,
        string $sessionId,
        string $lang
    ): ICheckout {
        $plan = $this->resolvePlan($planName);

        try {
            $customer = $this->mollieApiClient->customers->create(
                [
                    'name'     => $email,
                    'email'    => $email,
                    'metadata' => [
                        'session' => $sessionId
                    ]
                ]
            );

            $this->logger->info('mollie customer created', [
                'customerId' => $customer->id,
                'session'    => $sessionId
            ]);

            $payment = $this->mollieApiClient->payments->create(
                [
                    'amount'       => [
                        'currency' => $plan['currency'],
                        'value'    => number_format($plan['price'], 2, '.', '')
                    ],
                    'customerId'   => $customer->id,
                    'sequenceType' => 'first',
                    'description'  => sprintf('Keestash %s subscription', $planName),
                    'redirectUrl'  => sprintf(
                        (string) $this->configService->getValue('mollie_redirect_url', ''),
                        $lang,
                        $sessionId
                    ),
                    'webhookUrl'   => (string) $this->configService->getValue('mollie_webhook_url', ''),
                    'metadata'     => [
                        'email'          => $email,
                        'session'        => $sessionId,
                        'plan'           => $planName,
                        'interval'       => $plan['interval'],
                        'customerId'     => $customer->id,
                        'language'       => $lang,
                        'isFirstPayment' => true
                    ]
                ]
            );

            $checkoutUrl = $payment->getCheckoutUrl();
            if (null === $checkoutUrl) {
                throw new PaymentException('mollie did not return a checkout url');
            }

            $this->logger->info('mollie first payment created', [
                'paymentId'  => $payment->id,
                'customerId' => $customer->id,
                'session'    => $sessionId
            ]);

            return new Checkout(
                checkoutUrl: $checkoutUrl,
                paymentId: $payment->id,
                customerId: $customer->id
            );
        } catch (PaymentException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error('error creating mollie subscription', [
                'exception' => $e->getMessage(),
                'session'   => $sessionId
            ]);
            throw new PaymentException('could not create subscription', 0, $e);
        }
    }

    #[\Override]
    public function getPayment(string $paymentId): IPayment {
        try {
            $payment = $this->mollieApiClient->payments->get($paymentId);
            /** @var array<string, mixed> $metadata */
            $metadata = (array) json_decode(
                (string) json_encode($payment->metadata),
                true
            );
            /** @var array<string, mixed> $payload */
            $payload = (array) json_decode(
                (string) json_encode($payment),
                true
            );

            $amountRaw   = is_array($payload['amount'] ?? null) ? $payload['amount'] : [];
            $amountValue = isset($amountRaw['value']) && is_numeric($amountRaw['value'])
                ? (float) $amountRaw['value']
                : null;
            $currency    = isset($amountRaw['currency']) && is_string($amountRaw['currency'])
                ? $amountRaw['currency']
                : null;

            // Surface the Mollie customer id, which lives at the top level of the
            // payment resource, into the metadata the webhook reads back. It is
            // needed to create the recurring subscription against the mandate the
            // first payment established.
            if (false === isset($metadata['customerId'])
                && isset($payload['customerId'])
                && is_string($payload['customerId'])
            ) {
                $metadata['customerId'] = $payload['customerId'];
            }

            return new Payment(
                id: $payment->id,
                status: Type::from((string) $payment->status),
                metadata: $metadata,
                payload: $payload,
                amount: $amountValue,
                currency: $currency
            );
        } catch (Throwable $e) {
            $this->logger->error('error fetching mollie payment', [
                'exception' => $e->getMessage(),
                'paymentId' => $paymentId
            ]);
            throw new PaymentException('could not fetch payment', 0, $e);
        }
    }

    #[\Override]
    public function startRecurringBilling(
        string $customerId,
        string $planName,
        string $email,
        string $sessionId
    ): string {
        $plan = $this->resolvePlan($planName);

        try {
            $subscription = $this->mollieApiClient->subscriptions->createForId(
                $customerId,
                [
                    'amount'      => [
                        'currency' => $plan['currency'],
                        'value'    => number_format($plan['price'], 2, '.', '')
                    ],
                    'interval'    => $plan['interval'],
                    'description' => sprintf('Keestash %s subscription', $planName),
                    'webhookUrl'  => (string) $this->configService->getValue('mollie_webhook_url', ''),
                    'metadata'    => [
                        'email'   => $email,
                        'session' => $sessionId,
                        'plan'    => $planName
                    ]
                ]
            );

            $this->logger->info('mollie subscription created', [
                'subscriptionId' => $subscription->id,
                'customerId'     => $customerId,
                'session'        => $sessionId
            ]);

            return (string) $subscription->id;
        } catch (Throwable $e) {
            $this->logger->error('error creating mollie subscription', [
                'exception'  => $e->getMessage(),
                'customerId' => $customerId,
                'session'    => $sessionId
            ]);
            throw new PaymentException('could not start recurring billing', 0, $e);
        }
    }

    #[\Override]
    public function cancelSubscriptionImmediately(string $customerId, string $subscriptionId): void {
        $this->cancelSubscription($customerId, $subscriptionId);
    }

    #[\Override]
    public function cancelSubscriptionToTheEndOfThePeriod(string $customerId, string $subscriptionId): void {
        // Mollie has no "cancel at period end" concept for mandate based
        // subscriptions; cancelling stops future charges while the already paid
        // period stays valid, which is the desired behaviour here.
        $this->cancelSubscription($customerId, $subscriptionId);
    }

    private function cancelSubscription(string $customerId, string $subscriptionId): void {
        try {
            $this->mollieApiClient->subscriptions->cancelForId($customerId, $subscriptionId);
        } catch (Throwable $e) {
            $this->logger->error('error cancelling mollie subscription', [
                'exception'      => $e->getMessage(),
                'customerId'     => $customerId,
                'subscriptionId' => $subscriptionId
            ]);
            throw new PaymentException('could not cancel subscription', 0, $e);
        }
    }

    /**
     * @return array{price: float, currency: string, interval: string}
     * @throws PaymentException
     */
    private function resolvePlan(string $planName): array {
        $plans = (array) $this->configService->getValue('mollie_plans', []);

        if (false === array_key_exists($planName, $plans)) {
            $planName = (string) $this->configService->getValue('mollie_default_plan', '');
        }

        if (false === array_key_exists($planName, $plans)) {
            throw new PaymentException(sprintf('unknown plan "%s"', $planName));
        }

        $plan = (array) $plans[$planName];

        return [
            'price'    => (float) ($plan['price'] ?? 0.0),
            'currency' => (string) ($plan['currency'] ?? 'EUR'),
            'interval' => (string) ($plan['interval'] ?? '1 month')
        ];
    }

}
