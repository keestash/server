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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\ForgotPassword\ConfigProvider;
use KSA\ForgotPassword\Controller\ForgotPassword;
use KSA\ForgotPassword\Controller\ResetPassword;

return [
    CoreConfigProvider::ROUTES                 => [
        [
            'path'         => ConfigProvider::FORGOT_PASSWORD
            , 'middleware' => ForgotPassword::class
            , 'name'       => ForgotPassword::class
        ],
        [
            'path'         => ConfigProvider::RESET_PASSWORD
            , 'middleware' => ResetPassword::class
            , 'name'       => ResetPassword::class
        ]
    ],
    CoreConfigProvider::PUBLIC_ROUTES          => [
        ConfigProvider::FORGOT_PASSWORD
        , ConfigProvider::RESET_PASSWORD
    ],
    CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
        ConfigProvider::FORGOT_PASSWORD  => 'forgot_password'
        , ConfigProvider::RESET_PASSWORD => 'reset_password'
    ],
    CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::FORGOT_PASSWORD  => 'forgot_password'
        , ConfigProvider::RESET_PASSWORD => 'reset_password'
    ]
];