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

namespace KSA\Activity\Repository;

use doganoo\DI\DateTime\IDateTimeService;
use Exception;
use KSA\Activity\Entity\IActivity;
use KSA\Activity\Exception\ActivityException;
use KSA\Activity\Exception\ActivityNotCreatedException;
use KSP\Core\Backend\IBackend;
use Psr\Log\LoggerInterface;

class ActivityRepository {

    public function __construct(
        private readonly IBackend           $backend
        , private readonly IDateTimeService $dateTimeService
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function insert(IActivity $activity): IActivity {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert('activity')
                ->values(
                    [
                        'activity_id'   => '?'
                        , 'app_id'      => '?'
                        , 'description' => '?'
                        , 'create_ts'   => '?'
                    ]
                )
                ->setParameter(0, $activity->getActivityId())
                ->setParameter(1, $activity->getAppId())
                ->setParameter(2, $activity->getDescription())
                ->setParameter(3, $this->dateTimeService->toYMDHIS($activity->getCreateTs()))
                ->executeStatement();

            $lastInsertId = $this->backend->getConnection()->lastInsertId();

            if (false === is_numeric($lastInsertId)) {
                $this->logger->error('error with creating activity');
                throw new ActivityNotCreatedException();
            }

            return $activity;

        } catch (Exception $exception) {
            $this->logger->error('error while creating user');
            throw new ActivityException();
        }
    }

}