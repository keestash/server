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

use KSA\ForgotPassword\Api\ForgotPassword;
use KSA\ForgotPassword\Api\ResetPassword;
use KSA\ForgotPassword\Factory\Api\ForgotPasswordFactory;
use KSA\ForgotPassword\Factory\Api\ResetPasswordFactory;
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public const FORGOT_PASSWORD = "/forgot_password[/]";
    public const RESET_PASSWORD  = "/reset_password/:token[/]";

    public function __invoke(): array {
        return [
            'dependencies'                   => [
                'factories' => [
                    // api
                    ForgotPassword::class              => ForgotPasswordFactory::class
                    , ResetPassword::class             => ResetPasswordFactory::class

                    // controller
                    , Controller\ForgotPassword::class => Factory\Controller\ForgotPasswordFactory::class
                    , Controller\ResetPassword::class  => Factory\Controller\ResetPasswordFactory::class
                ]
            ],
            IApp::CONFIG_PROVIDER_WEB_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES                 => [
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
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES          => [
                    ConfigProvider::FORGOT_PASSWORD
                    , ConfigProvider::RESET_PASSWORD
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::FORGOT_PASSWORD  => 'forgot_password'
                    , ConfigProvider::RESET_PASSWORD => 'reset_password'
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::FORGOT_PASSWORD  => 'forgot_password'
                    , ConfigProvider::RESET_PASSWORD => 'reset_password'
                ]
            ],
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES        => [
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
                    ]
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/forgot_password/submit[/]',
                    '/reset_password/update[/]'
                ]
            ],
            'templates'                      => [
                'paths' => [
                    'forgotPassword' => [__DIR__ . '/template/']
                ]
            ]
        ];
    }

}