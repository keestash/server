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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Install\Api\InstallApps;
use KSA\Install\Api\InstallConfiguration;
use KSA\Install\Controller\Controller;
use KSA\Install\Factory\Api\InstallAppsFactory;
use KSA\Install\Factory\Api\InstallConfigurationFactory;
use KSA\Install\Factory\Controller\ControllerFactory;
use KSP\Api\IVerb;

final class ConfigProvider {

    public const INSTALL                    = '/install[/]';
    public const INSTALL_ALL                = '/install/apps/all[/]';
    public const INSTALL_APPS_CONFIGURATION = '/install/apps/configuration[/]';
    public const APP_ID                     = 'install';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST            => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 4,
                    CoreConfigProvider::APP_NAME       => 'Install',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::INSTALL,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            'dependencies'                          => [
                'factories' => [
                    // api
                    InstallApps::class            => InstallAppsFactory::class
                    , InstallConfiguration::class => InstallConfigurationFactory::class

                    // controller
                    , Controller::class           => ControllerFactory::class
                ]
            ],
            CoreConfigProvider::WEB_ROUTER          => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::INSTALL
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES          => [
                    ConfigProvider::INSTALL
                ],
                CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::INSTALL => 'install'
                ],
                CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::INSTALL => 'install'
                ]
            ],
            CoreConfigProvider::API_ROUTER          => [
                CoreConfigProvider::ROUTES        => [
                    [
                        'path'         => ConfigProvider::INSTALL_ALL
                        , 'middleware' => InstallApps::class
                        , 'method'     => IVerb::POST
                        , 'name'       => InstallApps::class
                    ],
                    [
                        'path'         => ConfigProvider::INSTALL_APPS_CONFIGURATION
                        , 'middleware' => InstallConfiguration::class
                        , 'method'     => IVerb::GET
                        , 'name'       => InstallConfiguration::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES => [
                    ConfigProvider::INSTALL_ALL
                    , ConfigProvider::INSTALL
                    , ConfigProvider::INSTALL_APPS_CONFIGURATION
                ],
            ],
            CoreConfigProvider::INSTALL_APPS_ROUTES => [
                ConfigProvider::INSTALL
                , ConfigProvider::INSTALL_ALL
            ],
            'templates'                             => [
                'paths' => [
                    'install' => [__DIR__ . '/template/']
                ]
            ]
        ];
    }

}