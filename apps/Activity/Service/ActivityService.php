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

namespace KSA\Activity\Service;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Activity\Entity\Activity;
use KSA\Activity\Event\ActivityTriggeredEvent;
use KSP\Core\Service\Event\IEventService;
use Ramsey\Uuid\Uuid;

class ActivityService implements IActivityService {

    public function __construct(
        private readonly IEventService $eventService
    ) {
    }

    /**
     * @param string    $appId
     * @param string    $referenceKey
     * @param ArrayList $data
     * @return void
     */
    public function insertActivity(string $appId, string $referenceKey, ArrayList $data): void {
        $this->eventService->execute(
            new ActivityTriggeredEvent(
                new Activity(
                    Uuid::uuid4()->toString()
                    , $appId
                    , $referenceKey
                    , $data
                    , new DateTimeImmutable()
                )
            )
        );
    }

    /**
     * @param string $appId
     * @param string $referenceKey
     * @param string $description
     * @return void
     */
    public function insertActivityWithSingleMessage(string $appId, string $referenceKey, string $description): void {
        $list = new ArrayList();
        $list->add($description);
        $this->insertActivity($appId, $referenceKey, $list);
    }


}