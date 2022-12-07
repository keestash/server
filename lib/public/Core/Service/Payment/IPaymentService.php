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

use Psr\Http\Message\ServerRequestInterface;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Subscription;

interface IPaymentService {

    /**
     * @throws ApiErrorException
     */
    public function createSubscription(string $priceId): Session;

    /**
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(ServerRequestInterface $request): Event;

    /**
     * @param string $subscriptionId
     * @return Subscription
     * @throws ApiErrorException
     */
    public function cancelSubscriptionImmediately(string $subscriptionId): Subscription;

    /**
     * @param string $subscriptionId
     * @return Subscription
     * @throws ApiErrorException
     */
    public function cancelSubscriptionToTheEndOfThePeriod(string $subscriptionId): Subscription;

}