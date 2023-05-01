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

use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Activity\Entity\Activity;
use KSA\Activity\Entity\IActivity;
use KSA\Activity\Exception\ActivityException;
use KSA\Activity\Exception\ActivityNotCreatedException;
use KSA\Activity\Exception\ActivityNotFoundException;
use KSP\Core\Backend\IBackend;
use Psr\Log\LoggerInterface;

class ActivityRepository {

    public function __construct(
        private readonly IBackend           $backend
        , private readonly IDateTimeService $dateTimeService
        , private readonly LoggerInterface  $logger
    ) {
    }

    /**
     * @param string $activityId
     * @return IActivity
     * @throws ActivityException|ActivityNotFoundException
     */
    public function get(string $activityId): IActivity {
        try {
            $queryBuilder  = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder  = $queryBuilder->select(
                [
                    'a.activity_id'
                    , 'a.app_id'
                    , 'a.reference_key'
                    , 'a.create_ts'
                ]
            )
                ->from('activity', 'a')
                ->where('a.activity_id = ?')
                ->setParameter(0, $activityId);
            $result        = $queryBuilder->executeQuery();
            $activities    = $result->fetchAllNumeric();
            $activityCount = count($activities);

            if (0 === $activityCount) {
                throw new ActivityNotFoundException();
            }

            if ($activityCount > 1) {
                throw new ActivityException("found more then one user for the given name");
            }

            $row      = $activities[0];
            $activity = new Activity(
                $row[0]
                , $row[1]
                , $row[2]
                , new ArrayList()
                , $this->dateTimeService->fromString((string) $row[3])
            );

        } catch (Exception $e) {
            $message = 'error while retrieving the activity';
            $this->logger->error(
                $message
                , ['exception' => $e]
            );
            throw new ActivityException($message);
        }
        return $activity;
    }

    public function insert(IActivity $activity): IActivity {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert('activity')
                ->values(
                    [
                        'activity_id'     => '?'
                        , 'app_id'        => '?'
                        , 'reference_key' => '?'
                        , 'create_ts'     => '?'
                    ]
                )
                ->setParameter(0, $activity->getActivityId())
                ->setParameter(1, $activity->getAppId())
                ->setParameter(2, $activity->getReferenceKey())
                ->setParameter(3, $this->dateTimeService->toYMDHIS($activity->getCreateTs()))
                ->executeStatement();

            return $activity;

        } catch (Exception $exception) {
            $this->logger->error('error while inserting activity', ['exception' => $exception]);
            throw new ActivityException();
        }
    }

    public function insertDescription(string $description, string $activityId): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert('activity_data')
                ->values(
                    [
                        'description'   => '?'
                        , 'activity_id' => '?'
                        , 'create_ts'   => '?'
                    ]
                )
                ->setParameter(0, $description)
                ->setParameter(1, $activityId)
                ->setParameter(3, $this->dateTimeService->toYMDHIS(new DateTimeImmutable()))
                ->executeStatement();

            $lastInsertId = $this->backend->getConnection()->lastInsertId();

            if (false === is_numeric($lastInsertId)) {
                $this->logger->error('error with creating activity data');
                throw new ActivityNotCreatedException();
            }

        } catch (Exception $exception) {
            $this->logger->error('error while inserting activity', ['exception' => $exception]);
            throw new ActivityException();
        }
    }

}