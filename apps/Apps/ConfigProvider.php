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

namespace KSA\Apps;

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Apps\Api\UpdateApp;
use KSA\Apps\Controller\Controller;
use KSA\Apps\Factory\Api\UpdateAppFactory;
use KSA\Apps\Factory\Controller\ControllerFactory;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public const ROUTE_NAME_APPS = '/apps[/]';
    public const APP_ID          = 'apps';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST   => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 1,
                    CoreConfigProvider::APP_NAME       => 'Apps',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::ROUTE_NAME_APPS,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            'dependencies'                 => [
                'factories' => [
                    // api
                    UpdateApp::class  => UpdateAppFactory::class,

                    // controller
                    Controller::class => ControllerFactory::class,
                ]
            ],
            CoreConfigProvider::WEB_ROUTER => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::ROUTE_NAME_APPS
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ]
                ],
                CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::ROUTE_NAME_APPS => 'apps'
                ],
                CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::ROUTE_NAME_APPS => 'apps'
                ],
                CoreConfigProvider::SETTINGS               => [
                    ConfigProvider::ROUTE_NAME_APPS => [
                        'name'      => 'Apps'
                        , 'faClass' => "fas fa-user-circle"
                        , 'order'   => 3
                    ]
                ]
            ],
            CoreConfigProvider::API_ROUTER => [
                CoreConfigProvider::ROUTES => [
                    [
                        'path'         => '/apps/update[/]'
                        , 'middleware' => UpdateApp::class
                        , 'method'     => IVerb::POST
                        , 'name'       => UpdateApp::class
                    ]
                ]
            ],
            'templates'                    => [
                'paths' => [
                    'apps' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}