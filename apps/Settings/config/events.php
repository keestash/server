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

use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSA\Settings\Event\Listener\OrganizationAddedEventListener;
use KSA\Settings\Event\Listener\PostStateChange;
use KSA\Settings\Event\Listener\UpdateSettingsListener;
use KSA\Settings\Event\Organization\OrganizationAddedEvent;
use KSA\Settings\Event\SettingsChangedEvent;

return [
    OrganizationAddedEvent::class => [
        OrganizationAddedEventListener::class
    ]
    , UserStateDeleteEvent::class => [
        PostStateChange::class
    ]
    , SettingsChangedEvent::class => [
        UpdateSettingsListener::class
    ]
];