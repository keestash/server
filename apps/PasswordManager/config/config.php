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
use Keestash\Core\Service\Core\Event\ApplicationStartedEvent;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use KSA\PasswordManager\Command\Node\Credential\CreateCredential;
use KSA\PasswordManager\Command\Node\Folder\CreateFolder;
use KSA\PasswordManager\Event\Listener\AfterPasswordChanged;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\PasswordManager\Event\Listener\OrganizationChangeListener;
use KSA\PasswordManager\Event\Listener\PublicShare\RemoveExpired;
use KSA\PasswordManager\Event\NodeAddedToOrganizationEvent;
use KSA\PasswordManager\Event\NodeOrganizationUpdatedEvent;
use KSA\PasswordManager\Event\NodeRemovedFromOrganizationEvent;
use KSP\Core\DTO\File\IExtension;

return [
    ConfigProvider::DEPENDENCIES                => require __DIR__ . '/dependencies.php',
    ConfigProvider::API_ROUTER                  => require __DIR__ . '/api_router.php',
    ConfigProvider::WEB_ROUTER                  => require __DIR__ . '/web_router.php',
    ConfigProvider::APP_LIST                    => [
        \KSA\PasswordManager\ConfigProvider::APP_ID => [
            ConfigProvider::APP_ORDER      => 0,
            ConfigProvider::APP_NAME       => 'Password Manager',
            ConfigProvider::APP_BASE_ROUTE => \KSA\PasswordManager\ConfigProvider::PASSWORD_MANAGER,
            ConfigProvider::APP_VERSION    => 1,
        ],
    ],
    ConfigProvider::EVENTS                      => [
        UserCreatedEvent::class                   => [
            AfterRegistration::class
        ]
        , UserUpdatedEvent::class                 => [
            AfterPasswordChanged::class
        ]
        , ApplicationStartedEvent::class          => [
            RemoveExpired::class
        ]
        , NodeAddedToOrganizationEvent::class     => [
            OrganizationChangeListener::class
        ]
        , NodeRemovedFromOrganizationEvent::class => [
            OrganizationChangeListener::class
        ]
        , NodeOrganizationUpdatedEvent::class     => [
            OrganizationChangeListener::class
        ]
    ],
    ConfigProvider::COMMANDS                    => [
        CreateFolder::class
        , CreateCredential::class
    ],
    \KSA\PasswordManager\ConfigProvider::FILE_UPLOAD_ALLOWED_EXTENSIONS => [
        IExtension::DOC,
        IExtension::DOCX,
        IExtension::PNG,
        IExtension::JPEG,
        IExtension::JPG,
        IExtension::PDF,
        IExtension::JSON,
    ],
    'templates'                                 => [
        'paths' => [
            'passwordManager' => [__DIR__ . '/../template/password_manager']
            , 'publicShare'   => [__DIR__ . '/../template/public_share']
        ]
    ]

];