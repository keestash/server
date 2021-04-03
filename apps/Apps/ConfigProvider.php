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

use KSA\Apps\Api\UpdateApp;
use KSA\Apps\Factory\Api\UpdateAppFactory;
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public function __invoke(): array {
        return [
            'dependencies'                   => [
                'factories' => [
                    // api
                    UpdateApp::class => UpdateAppFactory::class,
                ]
            ],
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES => [
                    [
                        'path'         => '/apps/update[/]'
                        , 'middleware' => UpdateApp::class
                        , 'method'     => IVerb::POST
                        , 'name'       => UpdateApp::class
                    ]
                ]
            ]
        ];
    }

}