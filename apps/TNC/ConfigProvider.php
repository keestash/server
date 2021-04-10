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

namespace KSA\TNC;

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\TNC\Controller\Controller;
use KSA\TNC\Factory\Controller\ControllerFactory;

final class ConfigProvider {

    public const TERMS_AND_CONDITIONS = "/tnc[/]";
    public const APP_ID               = 'tnc';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST   => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 11,
                    CoreConfigProvider::APP_NAME       => 'About',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::TERMS_AND_CONDITIONS,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            'dependencies'                 => [
                'factories' => [
                    Controller::class => ControllerFactory::class
                ]
            ],
            CoreConfigProvider::WEB_ROUTER => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::TERMS_AND_CONDITIONS
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES          => [
                    ConfigProvider::TERMS_AND_CONDITIONS
                ],
                CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::TERMS_AND_CONDITIONS => 'tnc'
                ],
            ],
            'templates'                    => [
                'paths' => [
                    'tnc' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}