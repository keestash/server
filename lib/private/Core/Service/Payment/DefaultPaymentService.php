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

use Keestash\Exception\Payment\ServiceNotImplementedException;
use KSP\Core\Service\Payment\IPaymentService;
use Psr\Http\Message\ServerRequestInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Subscription;

class DefaultPaymentService implements IPaymentService {

    #[\Override]
    public function createSubscription(string $priceId): Session {
        throw new ServiceNotImplementedException();
    }

    #[\Override]
    public function constructWebhookEvent(ServerRequestInterface $request): Event {
        throw new ServiceNotImplementedException();
    }

    #[\Override]
    public function cancelSubscriptionImmediately(string $subscriptionId): Subscription {
        throw new ServiceNotImplementedException();
    }

    #[\Override]
    public function cancelSubscriptionToTheEndOfThePeriod(string $subscriptionId): Subscription {
        throw new ServiceNotImplementedException();
    }

    #[\Override]
    public function getCustomer(string $id): Customer {
        throw new ServiceNotImplementedException();
    }

}