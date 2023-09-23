<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

enum Type: string {

    case CHECKOUT_SESSION_COMPLETED = 'checkout.session.completed';
    case INVOICE_PAID = 'invoice.paid';
    case INVOICE_PAYMENT_FAILED = 'invoice.payment_failed';
    case PAYMENT_INTENT_CREATED = 'payment_intent.created';
    case CUSTOMER_CREATED = 'customer.created';
    case CUSTOMER_SUBSCRIPTION_DELETED = 'customer.subscription.deleted';
    case CUSTOMER_SUBSCRIPTION_UPDATED = 'customer.subscription.updated';
    case CHARGE_SUCCEEDED = 'charge.succeeded';
    case PAYMENT_INTENT_SUCCEEDED = 'payment_intent.succeeded';
    case CUSTOMER_SUBSCRIPTION_CREATED = 'customer.subscription.created';
    case DELIMITER = '######DELIMITER######';

}
