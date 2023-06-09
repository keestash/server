<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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
use KSA\Activity\Event\Listener\ActivityTriggeredListener;
use KSA\Activity\Event\Listener\RemoveReferenceListener;
use KSA\Activity\Factory\Event\Listener\ActivityTriggeredListenerFactory;
use KSA\Activity\Factory\Event\Listener\RemoveReferenceListenerFactory;
use KSA\Activity\Factory\Repository\ActivityRepositoryFactory;
use KSA\Activity\Factory\Service\ActivityServiceFactory;
use KSA\Activity\Repository\ActivityRepository;
use KSA\Activity\Repository\IActivityRepository;
use KSA\Activity\Service\ActivityService;
use KSA\Activity\Service\IActivityService;

return [
    ConfigProvider::FACTORIES => [
        // event
        // --- listener
        ActivityTriggeredListener::class => ActivityTriggeredListenerFactory::class
        , RemoveReferenceListener::class => RemoveReferenceListenerFactory::class

        // repository
        , ActivityRepository::class      => ActivityRepositoryFactory::class

        // service
        , ActivityService::class         => ActivityServiceFactory::class
    ],
    ConfigProvider::ALIASES   => [
        IActivityService::class      => ActivityService::class
        , IActivityRepository::class => ActivityRepository::class
    ]
];