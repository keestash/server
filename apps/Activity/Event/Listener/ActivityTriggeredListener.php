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

namespace KSA\Activity\Event\Listener;

use KSA\Activity\Event\ActivityTriggeredEvent;
use KSA\Activity\Exception\ActivityNotFoundException;
use KSA\Activity\Repository\ActivityRepository;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;

class ActivityTriggeredListener implements IListener {

    public function __construct(
        private readonly ActivityRepository $activityRepository
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function execute(IEvent|ActivityTriggeredEvent $event): void {
        try {
            $this->logger->debug('retrieving the activity', ['activity' => $event->getActivity()]);
            $this->activityRepository->get($event->getActivity()->getActivityId());
            $this->logger->debug('activity found. Processing with this one');
        } catch (ActivityNotFoundException) {
            $this->logger->debug('no activity found. Inserting ....');
            $this->activityRepository->insert($event->getActivity());
            $this->logger->debug('done');
        }
    }

}