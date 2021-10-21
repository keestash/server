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

use Keestash\ConfigProvider;
use KSA\GeneralApi\Api\Demo\AddEmailAddress;
use KSA\GeneralApi\Api\MinimumCredential;
use KSA\GeneralApi\Api\Strings\GetAll;
use KSA\GeneralApi\Api\Thumbnail\File;
use KSA\GeneralApi\Api\Thumbnail\Get;
use KSA\GeneralApi\Api\UserList;
use KSP\Core\DTO\Http\IVerb;

return [
    ConfigProvider::ROUTES        => [
        [
            'path'         => \KSA\GeneralApi\ConfigProvider::THUMBNAIL_BY_EXTENSION
            , 'middleware' => Get::class
            , 'method'     => IVerb::GET
            , 'name'       => Get::class
        ],
        [
            'path'         => '/demousers/user/add[/]'
            , 'middleware' => AddEmailAddress::class
            , 'method'     => IVerb::POST
            , 'name'       => AddEmailAddress::class
        ],

        [
            'path'         => '/password_requirements[/]'
            , 'middleware' => MinimumCredential::class
            , 'method'     => IVerb::GET
            , 'name'       => MinimumCredential::class
        ],
        [
            'path'         => '/users/all/:type[/]'
            , 'middleware' => UserList::class
            , 'method'     => IVerb::GET
            , 'name'       => UserList::class
        ],
        [
            'path'         => '/frontend_templates/all[/]'
            , 'middleware' => \KSA\GeneralApi\Api\Template\GetAll::class
            , 'method'     => IVerb::GET
            , 'name'       => \KSA\GeneralApi\Api\Template\GetAll::class
        ],
        [
            'path'         => '/frontend_strings/all[/]'
            , 'middleware' => GetAll::class
            , 'method'     => IVerb::GET
            , 'name'       => GetAll::class
        ],
        [
            'path'         => '/icon/file/get/:extension/'
            , 'middleware' => File::class
            , 'method'     => IVerb::GET
            , 'name'       => File::class
        ],
    ],
    ConfigProvider::PUBLIC_ROUTES => [
        '/password_requirements[/]'
        , '/demousers/user/add[/]'
        , '/frontend_strings/all[/]'
        , '/frontend_templates/all[/]'
        , '/icon/file/get/:extension/'
    ]
];