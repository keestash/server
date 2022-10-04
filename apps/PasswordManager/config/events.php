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

use Keestash\Core\DTO\Event\ApplicationStartedEvent;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use KSA\PasswordManager\Event\Listener\AfterPasswordChanged;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\PasswordManager\Event\Listener\CredentialChangedListener;
use KSA\PasswordManager\Event\Listener\OrganizationChangeListener;
use KSA\PasswordManager\Event\Listener\PublicShare\RemoveExpired;
use KSA\PasswordManager\Event\Node\Credential\CredentialCreatedEvent;
use KSA\PasswordManager\Event\Node\Credential\CredentialUpdatedEvent;
use KSA\PasswordManager\Event\NodeAddedToOrganizationEvent;
use KSA\PasswordManager\Event\NodeOrganizationUpdatedEvent;
use KSA\PasswordManager\Event\NodeRemovedFromOrganizationEvent;

return [
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
    ],
    CredentialCreatedEvent::class             => [
        CredentialChangedListener::class
    ],
    CredentialUpdatedEvent::class             => [
        CredentialChangedListener::class
    ]
];