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

namespace KSP\Core\DTO\Payment;

/**
 * Result of creating a Mollie first payment for a subscription. Holds the
 * checkout URL the client is redirected to as well as the Mollie payment and
 * customer identifiers required to correlate later webhook notifications.
 */
interface ICheckout {

    public function getCheckoutUrl(): string;

    public function getPaymentId(): string;

    public function getCustomerId(): string;

}
