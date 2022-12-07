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
         * The api key for stripe
         * Used for Payment
         */
        , 'stripe_api_key'        => 'your-stripe-api-key-goes-here'

        /*
         * The secret for stripe webhook request
         * Used for Payment
         */
        , 'stripe_webhook_secret' => 'your-stripe-wh-secret-goes-here'

        /*
         * The price id charged for keestash
         */
        , 'stripe_price_id'          => 'your-stripe-price-id-goes-here'

        /*
         * The frontend url
         */
        , 'frontend_url'          => 'https://app.keestash.com'


    ];
