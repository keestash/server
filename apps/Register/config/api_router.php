<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Register\Api\MinimumCredential;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Exists;
use KSA\Register\Api\User\MailExists;
use KSA\Register\ConfigProvider;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::REGISTER_ADD,
        ConfigProvider::PASSWORD_REQUIREMENTS
    ],
    CoreConfigProvider::ROUTES        => [
        [
            'path'       => ConfigProvider::REGISTER_ADD,
            'middleware' => Add::class,
            'method'     => IVerb::POST,
            'name'       => Add::class
        ],
        [
            'path'       => '/user/mail/exists/:address[/]',
            'middleware' => MailExists::class,
            'method'     => IVerb::GET,
            'name'       => MailExists::class
        ],
        [
            'path'       => '/user/exists/:userName[/]',
            'middleware' => Exists::class,
            'method'     => IVerb::GET,
            'name'       => Exists::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_REQUIREMENTS
            , 'middleware' => MinimumCredential::class
            , 'method'     => IVerb::GET
            , 'name'       => MinimumCredential::class
        ],
    ]
];