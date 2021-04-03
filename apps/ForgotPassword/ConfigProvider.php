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

namespace KSA\ForgotPassword;

use KSA\ForgotPassword\Api\ForgotPassword;
use KSA\ForgotPassword\Api\ResetPassword;
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public function __invoke(): array {
        return [
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES        => [
                    [
                        'path'         => '/forgot_password/submit[/]'
                        , 'middleware' => ForgotPassword::class
                        , 'method'     => IVerb::POST
                        , 'name'       => ForgotPassword::class
                    ],
                    [
                        'path'         => '/reset_password/update[/]'
                        , 'middleware' => ResetPassword::class
                        , 'method'     => IVerb::POST
                        , 'name'       => ResetPassword::class
                    ]
                ],
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/forgot_password/submit[/]',
                    '/reset_password/update[/]'
                ]
            ]
        ];
    }

}