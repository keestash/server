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

namespace KSA\InstallInstance;

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\InstallInstance\Api\Config\Get;
use KSA\InstallInstance\Api\Config\Update;
use KSA\InstallInstance\Api\EndUpdate\EndUpdate;
use KSA\InstallInstance\Command\DemoMode;
use KSA\InstallInstance\Command\Uninstall;
use KSA\InstallInstance\Controller\Controller;
use KSA\InstallInstance\Factory\Api\Config\GetFactory;
use KSA\InstallInstance\Factory\Api\Config\UpdateFactory;
use KSA\InstallInstance\Factory\Api\EndUpdate\EndUpdateFactory;
use KSA\InstallInstance\Factory\Command\DemoModeFactory;
use KSA\InstallInstance\Factory\Command\UninstallFactory;
use KSA\InstallInstance\Factory\Controller\ControllerFactory;
use KSP\Api\IVerb;

final class ConfigProvider {

    public const CONFIG_PROVIDER_INSTALLATION_ROUTES = 'routes.installation.provider.config';
    public const INSTALL_INSTANCE                    = '/install_instance[/]';
    public const INSTALL_INSTANCE_CONFIG_DATA        = '/install_instance/config_data[/]';
    public const INSTALL_INSTANCE_END_UPDATE         = '/install_instance/end_update[/]';
    public const INSTALL_INSTANCE_UPDATE_CONFIG      = '/install_instance/update_config[/]';
    public const APP_ID                              = 'install_instance';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST                => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 6,
                    CoreConfigProvider::APP_NAME       => 'Install Instance',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::INSTALL_INSTANCE,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            CoreConfigProvider::WEB_ROUTER              => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::INSTALL_INSTANCE
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES          => [
                    ConfigProvider::INSTALL_INSTANCE
                ],
                CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::INSTALL_INSTANCE => 'install_instance'
                ],
                CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::INSTALL_INSTANCE => 'install_instance'
                ],
            ],
            CoreConfigProvider::INSTALL_INSTANCE_ROUTES => [
                ConfigProvider::INSTALL_INSTANCE
                , ConfigProvider::INSTALL_INSTANCE_UPDATE_CONFIG
                , ConfigProvider::INSTALL_INSTANCE_CONFIG_DATA
                , ConfigProvider::INSTALL_INSTANCE_END_UPDATE
            ],
            'dependencies'                              => [
                'factories' => [
                    // api
                    Get::class        => GetFactory::class,
                    Update::class     => UpdateFactory::class,
                    EndUpdate::class  => EndUpdateFactory::class,

                    // command
                    DemoMode::class   => DemoModeFactory::class,
                    Uninstall::class  => UninstallFactory::class,

                    // controller
                    Controller::class => ControllerFactory::class,
                ]
            ],
            CoreConfigProvider::API_ROUTER              => [
                CoreConfigProvider::ROUTES        => [
                    [
                        'path'         => ConfigProvider::INSTALL_INSTANCE_UPDATE_CONFIG
                        , 'middleware' => Update::class
                        , 'method'     => IVerb::POST
                        , 'name'       => Update::class
                    ],
                    [
                        'path'         => ConfigProvider::INSTALL_INSTANCE_CONFIG_DATA
                        , 'middleware' => Get::class
                        , 'method'     => IVerb::GET
                        , 'name'       => Get::class
                    ],
                    [
                        'path'         => ConfigProvider::INSTALL_INSTANCE_END_UPDATE
                        , 'middleware' => EndUpdate::class
                        , 'method'     => IVerb::POST
                        , 'name'       => EndUpdate::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES => [
                    ConfigProvider::INSTALL_INSTANCE_END_UPDATE
                    , ConfigProvider::INSTALL_INSTANCE_UPDATE_CONFIG
                    , ConfigProvider::INSTALL_INSTANCE_CONFIG_DATA
                ]
            ],
            CoreConfigProvider::COMMANDS                => [
                Uninstall::class
                , DemoMode::class
            ],
            'templates'                                 => [
                'paths' => [
                    'installInstance' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}