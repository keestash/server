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

namespace KSA\Settings;

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Settings\Controller\SettingsController;
use KSA\Settings\Factory\Controller\SettingsControllerFactory;
use KSA\Settings\Service\SettingService;
use Laminas\ServiceManager\Factory\InvokableFactory;

final class ConfigProvider {

    public const SETTINGS = "/settings[/]";
    public const APP_ID   = 'settings';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST   => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 10,
                    CoreConfigProvider::APP_NAME       => 'Settings',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::SETTINGS,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            'dependencies'                 => [
                'factories' => [
                    SettingService::class     => InvokableFactory::class,
                    SettingsController::class => SettingsControllerFactory::class
                ]
            ],
            CoreConfigProvider::WEB_ROUTER => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::SETTINGS
                        , 'middleware' => SettingsController::class
                        , 'name'       => SettingsController::class
                    ],
                ],
                CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::SETTINGS => 'settings'
                ],
                CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::SETTINGS => 'settings'
                ],
                CoreConfigProvider::SETTINGS               => [
                    ConfigProvider::SETTINGS => [
                        'name'      => 'Settings'
                        , 'faClass' => "fas fa-sliders-h"
                        , 'order'   => 1
                    ]
                ]
            ],
            'templates'                    => [
                'paths' => [
                    'settings' => [__DIR__ . '/template/']
                ]
            ]
        ];
    }

}