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

namespace KSA\About;

use KSA\About\Controller\Controller;
use KSA\About\Factory\Controller\ControllerFactory;
use KSP\App\IApp;

final class ConfigProvider {

    public const ABOUT = '/about[/]';

    public function __invoke(): array {
        return [
            'dependencies'                   => [
                'factories' => [
                    Controller::class => ControllerFactory::class,
                ]
            ],
            IApp::CONFIG_PROVIDER_WEB_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES                 => [
                    [
                        'path'         => ConfigProvider::ABOUT
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ]
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::ABOUT => 'about'
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS     => [],
                IApp::CONFIG_PROVIDER_SETTINGS               => [
                    ConfigProvider::ABOUT => [
                        'name'      => 'about'
                        , 'faClass' => 'fas fa-info'
                        , 'order'   => 3
                    ]
                ]
            ],
            'templates' => [
                'paths' => [
                    'about' => [__DIR__ . '/template'],
                ],
            ]
        ];
    }

}