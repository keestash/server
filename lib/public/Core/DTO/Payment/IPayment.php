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
 * A Mollie payment as fetched from the API when handling a webhook. Carries the
 * verified status and the metadata that correlates the payment back to the
 * registration that started it, plus the full decoded payload for logging.
 */
interface IPayment {

    public function getId(): string;

    public function getStatus(): Type;

    /**
     * The charged amount as reported by Mollie (amount.value), or null when the
     * payment carries no amount. Used to verify the paid amount matches the plan
     * the registration was created for.
     */
    public function getAmount(): ?float;

    /**
     * The ISO currency of the charged amount (amount.currency), or null.
     */
    public function getCurrency(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;

}
