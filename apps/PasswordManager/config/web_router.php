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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Controller\Attachment\View;
use KSA\PasswordManager\Controller\PublicShare\PublicShareController;
use KSP\Api\IRoute;

return [
    CoreConfigProvider::ROUTES                   => [
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_VIEW
            , IRoute::MIDDLEWARE => View::class
            , IRoute::NAME       => View::class
        ]
        , [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER
            , IRoute::MIDDLEWARE => \KSA\PasswordManager\Controller\PasswordManager\Controller::class
            , IRoute::NAME       => \KSA\PasswordManager\Controller\PasswordManager\Controller::class
        ]
        , [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE
            , IRoute::MIDDLEWARE => PublicShareController::class
            , IRoute::NAME       => PublicShareController::class
        ]
    ]
    , CoreConfigProvider::PUBLIC_ROUTES          => [
        ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE
    ]
    , CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::PASSWORD_MANAGER                       => 'password_manager'
        , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE => 'public_share'
    ],
    CoreConfigProvider::WEB_ROUTER_SCRIPTS       => [
        ConfigProvider::PASSWORD_MANAGER                       => 'password_manager'
        , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE => 'public_share'
    ]
];