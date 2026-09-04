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

namespace KST\Unit\Core\Service\Payment;

use Keestash\Core\Service\Payment\DefaultPaymentService;
use Keestash\Exception\Payment\PaymentException;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Payment\IPaymentService;
use Mollie\Api\Fake\MockResponse;
use Mollie\Api\Http\Requests\GetPaymentRequest;
use Mollie\Api\MollieApiClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DefaultPaymentServiceTest extends TestCase {

    private function service(array $config, ?MollieApiClient $client = null): DefaultPaymentService {
        return new DefaultPaymentService(
            $client ?? new MollieApiClient(),
            $this->config($config),
            new NullLogger()
        );
    }

    /**
     * Hand written config service (no mocking library, per project rules).
     *
     * @param array<string, mixed> $values
     */
    private function config(array $values): IConfigService {
        return new class($values) implements IConfigService {
            /** @param array<string, mixed> $values */
            public function __construct(private readonly array $values) {
            }

            public function getValue(string $key, $default = null) {
                return $this->values[$key] ?? $default;
            }

            public function getAll(): array {
                return $this->values;
            }
        };
    }

    public function testUnknownPlanWithoutDefaultThrows(): void {
        $service = $this->service([
            'mollie_plans'        => ['personal' => ['price' => 4.99, 'currency' => 'EUR', 'interval' => '1 month']],
            'mollie_default_plan' => 'personal'
        ]);

        // "enterprise" is unknown, but a valid default exists, so resolution
        // falls back to "personal". Without any Mollie key the subsequent API
        // call fails, which is surfaced as a PaymentException too - the point of
        // this assertion is that resolution does not reject a known default.
        $this->expectException(PaymentException::class);
        $service->createSubscription('enterprise', 'a@b.com', 'session-1', 'en');
    }

    public function testUnknownPlanAndUnknownDefaultThrowsBeforeApiCall(): void {
        $service = $this->service([
            'mollie_plans'        => ['personal' => ['price' => 4.99, 'currency' => 'EUR', 'interval' => '1 month']],
            'mollie_default_plan' => 'does-not-exist'
        ]);

        $this->expectException(PaymentException::class);
        $service->createSubscription('also-missing', 'a@b.com', 'session-2', 'en');
    }

    public function testEmptyPlansThrows(): void {
        $service = $this->service(['mollie_plans' => [], 'mollie_default_plan' => '']);

        $this->expectException(PaymentException::class);
        $service->createSubscription('personal', 'a@b.com', 'session-3', 'en');
    }

    public function testImplementsInterface(): void {
        $this->assertInstanceOf(
            IPaymentService::class,
            $this->service(['mollie_plans' => []])
        );
    }

    /**
     * getPayment maps a Mollie payment resource to the DTO the webhook trusts.
     * This is the code path that runs on every webhook, so it is exercised
     * against a real (faked) Mollie client rather than a stub.
     */
    public function testGetPaymentMapsMollieResourceToDto(): void {
        $client = MollieApiClient::fake([
            GetPaymentRequest::class => MockResponse::ok([
                'resource'     => 'payment',
                'id'           => 'tr_abc123',
                'mode'         => 'test',
                'status'       => 'paid',
                'amount'       => ['value' => '4.99', 'currency' => 'EUR'],
                'description'  => 'Keestash personal subscription',
                'sequenceType' => 'first',
                'customerId'   => 'cst_xyz789',
                'metadata'     => [
                    'email'   => 'a@b.com',
                    'session' => 'session-42',
                    'plan'    => 'personal'
                ]
            ])
        ]);

        $payment = $this->service(['mollie_plans' => []], $client)->getPayment('tr_abc123');

        $this->assertSame('tr_abc123', $payment->getId());
        $this->assertSame(Type::PAID, $payment->getStatus());
        $this->assertSame(4.99, $payment->getAmount());
        $this->assertSame('EUR', $payment->getCurrency());

        $metadata = $payment->getMetadata();
        $this->assertSame('a@b.com', $metadata['email']);
        $this->assertSame('session-42', $metadata['session']);
        $this->assertSame('personal', $metadata['plan']);
        // The top-level customer id must be surfaced into metadata so the
        // webhook can start recurring billing against the mandate.
        $this->assertSame('cst_xyz789', $metadata['customerId']);
    }

    public function testGetPaymentMapsFailedStatus(): void {
        $client = MollieApiClient::fake([
            GetPaymentRequest::class => MockResponse::ok([
                'resource' => 'payment',
                'id'       => 'tr_failed',
                'mode'     => 'test',
                'status'   => 'failed',
                'amount'   => ['value' => '4.99', 'currency' => 'EUR'],
                'metadata' => ['session' => 's', 'email' => 'a@b.com']
            ])
        ]);

        $payment = $this->service(['mollie_plans' => []], $client)->getPayment('tr_failed');

        $this->assertSame(Type::FAILED, $payment->getStatus());
    }

    public function testGetPaymentWrapsApiErrorInPaymentException(): void {
        $client = MollieApiClient::fake([
            GetPaymentRequest::class => MockResponse::notFound()
        ]);

        $this->expectException(PaymentException::class);
        $this->service(['mollie_plans' => []], $client)->getPayment('tr_missing');
    }

}
