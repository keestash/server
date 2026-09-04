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

use KSP\Core\DTO\Payment\ICheckout;

final readonly class Checkout implements ICheckout {

    public function __construct(
        private string $checkoutUrl,
        private string $paymentId,
        private string $customerId
    ) {
    }

    #[\Override]
    public function getCheckoutUrl(): string {
        return $this->checkoutUrl;
    }

    #[\Override]
    public function getPaymentId(): string {
        return $this->paymentId;
    }

    #[\Override]
    public function getCustomerId(): string {
        return $this->customerId;
    }

}
