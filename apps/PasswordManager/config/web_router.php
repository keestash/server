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

use Keestash\ConfigProvider as ConfigProviderAlias;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Controller\Attachment\View;
use KSA\PasswordManager\Controller\PublicShare\PublicShareController;

return [
    ConfigProviderAlias::ROUTES => [
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_VIEW
            , 'middleware' => View::class
            , 'name'       => View::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER
            , 'middleware' => \KSA\PasswordManager\Controller\PasswordManager\Controller::class
            , 'name'       => \KSA\PasswordManager\Controller\PasswordManager\Controller::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE
            , 'middleware' => PublicShareController::class
            , 'name'       => PublicShareController::class
        ],
    ],
    ConfigProviderAlias::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::PASSWORD_MANAGER                       => 'password_manager'
        , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE => 'public_share'
    ],
    ConfigProviderAlias::WEB_ROUTER_SCRIPTS => [
        ConfigProvider::PASSWORD_MANAGER                       => 'password_manager'
        , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE => 'public_share'
    ]
];