<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

$CONFIG =
    [
        /*
         * Whether errors should be shown on the UI or not
         */
        "show_errors"             => false

        /*
         * If the application should behave in debug
         */
        , "debug"                 => false

        /*
         * The database host
         */
        , "db_host"               => ""

        /*
         * The database user
         */
        , "db_user"               => ""

        /*
         * The database password
         */
        , "db_password"           => ""

        /*
         * The database password
         */
        , "db_name"               => ""

        /*
         * The database password
         */
        , "db_port"               => ""

        /*
         * The database password
         */
        , "db_charset"            => ""

        /*
         * Whether the api requests should
         * be logged
         */
        , "log_requests"          => false

        /*
         * How long a user should be logged in
         */
        , "user_lifetime"         => 0

        /*
         * The SMTP host to send emails
         */
        , "email_smtp_host"       => ""

        /*
         * The email address from which mails are sent
         */
        , "email_user"            => ""

        /*
         * The username to the email address to display
         */
        , "email_user_name"       => ""

        /*
        * The mail clients protocol (tls, ssl)
        */
        , 'email_protocol'        => 'ssl'

        /*
        * The mail clients port
        */
        , 'email_port'            => 0

        /*
         * The password belonging to email_user
         */
        , "email_password"        => ""

        /*
         * The redis server host used to cache data
         */
        , 'redis_server'          => '127.0.0.1'

        /*
         * The redis server port used to cache data
         */
        , 'redis_port'            => 6379

        /*
         * The verbosity of the logger.
         * Uses Monolog Log levels
         */
        , 'log_level'             => 100

        /*
         * The api key for HIBP
         * Used for Password Health Check
         */
        , 'hibp_api_key'          => 'your-api-key-goes-here'

        /*
         * The dsn for Sentry
         * Used for Monitoring
         */
        , 'sentry_dsn'            => 'your-sentry-api-key-goes-here'

        /*
         * The api key for Mollie
         * Used for Payment
         */
        , 'mollie_api_key'        => 'your-mollie-api-key-goes-here'

        /*
         * The plan selected when no plan is passed on registration.
         * Must be a key present in mollie_plans below.
         */
        , 'mollie_default_plan'   => 'personal'

        /*
         * The URL the customer returns to after paying on Mollie.
         * %1$s is replaced with the language, %2$s with the payment session id.
         */
        , 'mollie_redirect_url'   => 'https://app.keestash.com/%1$s/subscribed?session=%2$s'

        /*
         * The publicly reachable URL Mollie calls to notify about payment status
         * changes. Must point to the /payment/webhook endpoint of this instance.
         */
        , 'mollie_webhook_url'    => 'https://api.keestash.com/payment/webhook'

        /*
         * Optional signing secret for Mollie's signed ("next-gen") webhooks,
         * taken from the Mollie dashboard. When set, webhook requests that carry
         * an X-Mollie-Signature header are HMAC-verified and rejected on
         * mismatch. Legacy payment webhooks (no signature) are unaffected and
         * remain verified by re-fetching the payment from the API. Leave empty
         * to disable signature verification.
         */
        , 'mollie_webhook_secret' => ''

        /*
         * Config driven subscription plans. Each plan defines the price, the
         * currency and the billing interval used for the first (mandate
         * establishing) payment.
         */
        , 'mollie_plans'          => [
            'personal' => ['price' => 4.99, 'currency' => 'EUR', 'interval' => '1 month'],
        ]

        /*
         * The frontend url
         */
        , 'frontend_url'          => 'https://app.keestash.com'

        /*
         * The namespace for prometheus
         */
        , 'prometheus_namespace'  => 'keestash'

        /*
         * The prefix for prometheus
         */
        , 'prometheus_prefix'     => ''
    ];
