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
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public const CONFIG_PROVIDER_INSTALLATION_ROUTES = 'routes.installation.provider.config';
    public const INSTALL_INSTANCE                    = '/install_instance[/]';

    public function __invoke(): array {
        return [
            IApp::CONFIG_PROVIDER_WEB_ROUTER                  => [
                IApp::CONFIG_PROVIDER_ROUTES                 => [
                    [
                        'path'         => ConfigProvider::INSTALL_INSTANCE
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES          => [
                    ConfigProvider::INSTALL_INSTANCE
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::INSTALL_INSTANCE => 'install_instance'
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::INSTALL_INSTANCE => 'install_instance'
                ],
            ],
            \Keestash\ConfigProvider::INSTALL_INSTANCE_ROUTES => [
                ConfigProvider::INSTALL_INSTANCE
                , '/install_instance/update_config[/]'
                , '/install_instance/config_data[/]'
                , '/install_instance/end_update[/]'
            ],
            'dependencies'                                    => [
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
            IApp::CONFIG_PROVIDER_API_ROUTER                  => [
                IApp::CONFIG_PROVIDER_ROUTES        => [
                    [
                        'path'         => '/install_instance/update_config[/]'
                        , 'middleware' => Update::class
                        , 'method'     => IVerb::POST
                        , 'name'       => Update::class
                    ],
                    [
                        'path'         => '/install_instance/config_data[/]'
                        , 'middleware' => Get::class
                        , 'method'     => IVerb::GET
                        , 'name'       => Get::class
                    ],
                    [
                        'path'         => '/install_instance/end_update[/]'
                        , 'middleware' => EndUpdate::class
                        , 'method'     => IVerb::POST
                        , 'name'       => EndUpdate::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/install_instance/end_update[/]'
                    , '/install_instance/update_config[/]'
                    , '/install_instance/config_data[/]'
                ]
            ],
            IApp::CONFIG_PROVIDER_COMMANDS                    => [
                Uninstall::class
                , DemoMode::class
            ],
            'templates'                                       => [
                'paths' => [
                    'installInstance' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}