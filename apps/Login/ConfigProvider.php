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

namespace KSA\Login;

use KSA\Login\Api\Login;
use KSA\Login\Controller\Logout;
use KSA\Login\Factory\Api\LoginFactory;
use KSA\Login\Factory\Controller\LogoutFactory;
use KSA\Login\Service\TokenService;
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;
use Laminas\ServiceManager\Factory\InvokableFactory;

final class ConfigProvider {

    public function __invoke(): array {
        return [
            'dependencies'                   => [
                'factories' => [
                    // api
                    Login::class        => LoginFactory::class,

                    // controller
                    Logout::class       => LogoutFactory::class,

                    // service
                    TokenService::class => InvokableFactory::class
                ]
            ],
            IApp::CONFIG_PROVIDER_WEB_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES        => [
                    [
                        'path'         => '/logout[/]'
                        , 'middleware' => Logout::class
                        , 'method'     => IVerb::GET
                        , 'name'       => Logout::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/logout[/]'
                ]
            ],
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES        => [
                    [
                        'path'         => '/login/submit[/]'
                        , 'middleware' => Login::class
                        , 'method'     => IVerb::POST
                        , 'name'       => Login::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/login/submit[/]'
                ]
            ]
        ];
    }

}