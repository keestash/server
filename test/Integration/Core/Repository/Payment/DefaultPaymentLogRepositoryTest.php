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

namespace KST\Integration\Core\Repository\Payment;

use DateTimeImmutable;
use Keestash\Core\DTO\Payment\Log;
use Keestash\Exception\Payment\PaymentException;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class DefaultPaymentLogRepositoryTest extends TestCase {

    private IPaymentLogRepository $paymentLogRepository;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->paymentLogRepository = $this->getService(IPaymentLogRepository::class);
    }

    public function testInsertAndGet(): void {
        $key = Uuid::uuid4()->toString();
        $this->paymentLogRepository->insert(
            new Log(
                key: $key,
                log: ['status' => Type::PAID->value, 'paymentId' => 'tr_' . $key],
                createTs: new DateTimeImmutable()
            )
        );

        $log = $this->paymentLogRepository->get($key);
        $this->assertSame($key, $log->getKey());
        $this->assertSame(Type::PAID->value, $log->getLog()['status']);
        $this->assertSame('tr_' . $key, $log->getLog()['paymentId']);
    }

    public function testGetByType(): void {
        $key = Uuid::uuid4()->toString();
        $this->paymentLogRepository->insert(
            new Log(
                key: $key,
                log: ['status' => Type::FAILED->value],
                createTs: new DateTimeImmutable()
            )
        );

        $log = $this->paymentLogRepository->getByType(Type::FAILED);
        $this->assertSame(Type::FAILED->value, $log->getLog()['status']);
    }

    public function testGetUnknownKeyThrows(): void {
        $this->expectException(PaymentException::class);
        $this->paymentLogRepository->get(Uuid::uuid4()->toString());
    }

    public function testImplementsInterface(): void {
        $this->assertInstanceOf(IPaymentLogRepository::class, $this->paymentLogRepository);
    }

}
