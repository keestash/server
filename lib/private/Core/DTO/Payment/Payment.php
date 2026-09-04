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

namespace Keestash\Core\DTO\Payment;

use KSP\Core\DTO\Payment\IPayment;
use KSP\Core\DTO\Payment\Type;

final readonly class Payment implements IPayment {

    /**
     * @param array<string, mixed> $metadata
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string  $id,
        private Type    $status,
        private array   $metadata,
        private array   $payload,
        private ?float  $amount = null,
        private ?string $currency = null
    ) {
    }

    #[\Override]
    public function getId(): string {
        return $this->id;
    }

    #[\Override]
    public function getStatus(): Type {
        return $this->status;
    }

    #[\Override]
    public function getAmount(): ?float {
        return $this->amount;
    }

    #[\Override]
    public function getCurrency(): ?string {
        return $this->currency;
    }

    #[\Override]
    public function getMetadata(): array {
        return $this->metadata;
    }

    #[\Override]
    public function getPayload(): array {
        return $this->payload;
    }

}
