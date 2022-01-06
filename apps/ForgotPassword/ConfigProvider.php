<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\ForgotPassword;

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\ForgotPassword\Api\AccountDetails;
use KSA\ForgotPassword\Api\Configuration;
use KSA\ForgotPassword\Api\ForgotPassword;
use KSA\ForgotPassword\Api\ResetPassword;
use KSA\ForgotPassword\Factory\Api\AccountDetailsFactory;
use KSA\ForgotPassword\Factory\Api\ConfigurationFactory;
use KSA\ForgotPassword\Factory\Api\ForgotPasswordFactory;
use KSA\ForgotPassword\Factory\Api\ResetPasswordFactory;
use KSP\Api\IVerb;

final class ConfigProvider {

    public const FORGOT_PASSWORD                = "/forgot_password[/]";
    public const RESET_PASSWORD                 = "/reset_password/:token[/]";
    public const APP_ID                         = 'forgotPassword';
    public const FORGOT_PASSWORD_CONFIGURATION  = '/forgot_password/configuration[/]';
    public const RESET_PASSWORD_ACCOUNT_DETAILS = '/reset_password/account_details/:resetPasswordToken/';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST   => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 2,
                    CoreConfigProvider::APP_NAME       => 'Forgot Password',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::FORGOT_PASSWORD,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            'dependencies'                 => [
                'factories' => [
                    // api
                    ForgotPassword::class              => ForgotPasswordFactory::class
                    , ResetPassword::class             => ResetPasswordFactory::class
                    , Configuration::class             => ConfigurationFactory::class
                    , AccountDetails::class            => AccountDetailsFactory::class

                    // controller
                    , Controller\ForgotPassword::class => Factory\Controller\ForgotPasswordFactory::class
                    , Controller\ResetPassword::class  => Factory\Controller\ResetPasswordFactory::class
                ]
            ],
            CoreConfigProvider::WEB_ROUTER => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::FORGOT_PASSWORD
                        , 'middleware' => Controller\ForgotPassword::class
                        , 'name'       => Controller\ForgotPassword::class
                    ],
                    [
                        'path'         => ConfigProvider::RESET_PASSWORD
                        , 'middleware' => Controller\ResetPassword::class
                        , 'name'       => Controller\ResetPassword::class
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
            ],
            CoreConfigProvider::API_ROUTER => [
                CoreConfigProvider::ROUTES        => [
                    [
                        'path'         => '/forgot_password/submit[/]'
                        , 'middleware' => ForgotPassword::class
                        , 'method'     => IVerb::POST
                        , 'name'       => ForgotPassword::class
                    ],
                    [
                        'path'         => '/reset_password/update[/]'
                        , 'middleware' => ResetPassword::class
                        , 'method'     => IVerb::POST
                        , 'name'       => ResetPassword::class
                    ],
                    [
                        'path'         => ConfigProvider::FORGOT_PASSWORD_CONFIGURATION
                        , 'middleware' => Configuration::class
                        , 'method'     => IVerb::GET
                        , 'name'       => Configuration::class
                    ],
                    [
                        'path'         => ConfigProvider::RESET_PASSWORD_ACCOUNT_DETAILS
                        , 'middleware' => AccountDetails::class
                        , 'method'     => IVerb::GET
                        , 'name'       => AccountDetails::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES => [
                    '/forgot_password/submit[/]'
                    , '/reset_password/update[/]'
                    , ConfigProvider::FORGOT_PASSWORD_CONFIGURATION
                    , ConfigProvider::RESET_PASSWORD_ACCOUNT_DETAILS
                ]
            ],
            'templates'                    => [
                'paths' => [
                    'forgotPassword' => [__DIR__ . '/template/']
                ]
            ]
        ];
    }

}