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

namespace KSP\Core\Service\Payment;

use Keestash\Exception\Payment\PaymentException;
use KSP\Core\DTO\Payment\ICheckout;
use KSP\Core\DTO\Payment\IPayment;

interface IPaymentService {

    public const string PAYMENT_WEBHOOK_ENDPOINT = '/payment/webhook';

    /**
     * Creates a Mollie customer and a first (mandate establishing) payment for
     * the given plan and returns the checkout information the client needs to
     * redirect the user to Mollie's payment screen.
     *
     * @param string $planName  a key present in the mollie_plans config
     * @param string $email     the registering user's email address
     * @param string $sessionId correlation id shared with the payment metadata
     * @param string $lang      two letter language code used in the redirect url
     * @throws PaymentException
     */
    public function createSubscription(
        string $planName,
        string $email,
        string $sessionId,
        string $lang
    ): ICheckout;

    /**
     * Fetches the payment from Mollie and returns it as a DTO carrying the
     * verified status, the correlation metadata and the full payload. Fetching
     * the payment from the API is how a webhook notification is verified: the
     * webhook only carries an id, the trusted state comes from this call.
     *
     * @throws PaymentException
     */
    public function getPayment(string $paymentId): IPayment;

    /**
     * Creates the Mollie subscription that drives the recurring charges, using
     * the mandate the (already paid) first payment established for the customer.
     * A first payment alone only creates a mandate; without a subscription
     * Mollie never charges again. This is therefore called once the first
     * payment is confirmed paid (from the webhook), and returns the Mollie
     * subscription id needed to cancel it later.
     *
     * @param string $customerId the Mollie customer the mandate belongs to
     * @param string $planName   a key present in the mollie_plans config
     * @param string $email      the subscribing user's email address
     * @param string $sessionId  correlation id shared with the subscription metadata
     * @throws PaymentException
     */
    public function startRecurringBilling(
        string $customerId,
        string $planName,
        string $email,
        string $sessionId
    ): string;

    /**
     * @throws PaymentException
     */
    public function cancelSubscriptionImmediately(string $customerId, string $subscriptionId): void;

    /**
     * @throws PaymentException
     */
    public function cancelSubscriptionToTheEndOfThePeriod(string $customerId, string $subscriptionId): void;

}
