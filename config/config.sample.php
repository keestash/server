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
        "show_errors"       => false

        /*
         * If the application should behave in debug
         */
        , "debug"           => false

        /*
         * The database host
         */
        , "db_host"         => ""

        /*
         * The database user
         */
        , "db_user"         => ""

        /*
         * The database password
         */
        , "db_password"     => ""

        /*
         * The database password
         */
        , "db_name"         => ""

        /*
         * The database password
         */
        , "db_port"         => ""

        /*
         * The database password
         */
        , "db_charset"      => ""

        /*
         * Whether the api requests should
         * be logged
         */
        , "log_requests"    => false

        /*
         * How long a user should be logged in
         */
        , "user_lifetime"   => 0

        /*
         * The SMTP host to send emails
         */
        , "email_smtp_host" => ""

        /*
         * The email address from which mails are sent
         */
        , "email_user"      => ""

        /*
         * The password belonging to email_user
         */
        , "email_password"  => ""
    ];
