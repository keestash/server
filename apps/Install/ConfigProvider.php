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

namespace KSA\Install;

use KSA\Install\Api\InstallApps;
use KSA\Install\Command\Uninstall;
use KSA\Install\Controller\Controller;
use KSA\Install\Factory\Api\InstallAppsFactory;
use KSA\Install\Factory\Command\UninstallFactory;
use KSA\Install\Factory\Controller\ControllerFactory;
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public const INSTALL = '/install[/]';

    public function __invoke(): array {
        return [
            'dependencies'                   => [
                'factories' => [
                    // api
                    InstallApps::class => InstallAppsFactory::class,
                    Uninstall::class   => UninstallFactory::class,

                    // controller
                    Controller::class  => ControllerFactory::class,
                ]
            ],
            IApp::CONFIG_PROVIDER_WEB_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES                 => [
                    [
                        'path'         => ConfigProvider::INSTALL
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES          => [
                    ConfigProvider::INSTALL
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::INSTALL => 'install'
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS => [
                    ConfigProvider::INSTALL => 'install'
                ]
            ],
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES        => [
                    [
                        'path'         => '/install/apps/all[/]'
                        , 'middleware' => InstallApps::class
                        , 'method'     => IVerb::POST
                        , 'name'       => InstallApps::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/install/apps/all[/]'
                ]
            ],
            IApp::CONFIG_PROVIDER_COMMANDS   => [
                Uninstall::class
            ],
            'templates'                      => [
                'paths' => [
                    'install' => [__DIR__ . '/template/']
                ]
            ]
        ];
    }

}