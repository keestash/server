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

use KSA\Register\Event\Listener\ResetPasswordSendEmailListener;
use KSA\Register\Event\Listener\UserRegisteredEventListener;
use KSA\Register\Event\ResetPasswordEvent;
use KSA\Register\Event\UserRegisteredEvent;

return [
    UserRegisteredEvent::class => [
        UserRegisteredEventListener::class
    ],
    ResetPasswordEvent::class => [
        ResetPasswordSendEmailListener::class
    ]
];