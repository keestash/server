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

namespace KSA\GeneralApi\Repository;

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;

class OrganizationUserRepository implements IOrganizationUserRepository {

    private IUserRepository  $userRepository;
    private ILogger          $logger;
    private IDateTimeService $dateTimeService;
    private IBackend         $backend;

    public function __construct(
        IUserRepository $userRepository
        , IBackend $backend
        , ILogger $logger
        , IDateTimeService $dateTimeService
    ) {
        $this->userRepository  = $userRepository;
        $this->logger          = $logger;
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
    }

    public function getByOrganization(IOrganization $organization): IOrganization {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'organization_id'
                , 'user_id'
                , 'create_ts'
            ]
        )
            ->from('user_organization')
            ->where('organization_id = ?')
            ->setParameter(0, $organization->getId());

        $rows = $queryBuilder->execute();

        foreach ($rows as $row) {
            $user = $this->userRepository->getUserById($row['user_id']);
            $organization->addUser($user);
        }

        return $organization;
    }

    public function insert(IOrganization $organization): IOrganization {

        /** @var IUser $user */
        foreach ($organization->getUsers() as $user) {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert('user_organization')
                ->values(
                    [
                        'organization_id' => '?'
                        , 'user_id'       => '?'
                        , 'create_ts'     => '?'
                    ]
                )
                ->setParameter(0, $organization->getId())
                ->setParameter(1, $user->getId())
                ->setParameter(2,
                    $this->dateTimeService->toYMDHIS(new DateTime())
                )
                ->execute();

            $lastInsertId = $this->backend->getConnection()->lastInsertId();

            if (null === $lastInsertId) {
                throw new GeneralApiException();
            }
        }
        return $organization;
    }

    public function remove(IUser $user, IOrganization $organization): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('user_organization')
                ->where('user_id = ?')
                ->andWhere('organization_id = ?')
                ->setParameter(0, $user->getId())
                ->setParameter(1, $organization->getId())
                ->execute() !== 0;
    }

}