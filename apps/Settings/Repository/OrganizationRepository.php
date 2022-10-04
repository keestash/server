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

namespace KSA\Settings\Repository;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Organization\Organization;
use Keestash\Exception\OrganizationNotUpdatedException;
use KSA\Settings\Exception\OrganizationNotDeletedException;
use KSA\Settings\Exception\OrganizationNotFoundException;
use KSA\Settings\Exception\SettingsException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Logger\ILogger;

class OrganizationRepository implements IOrganizationRepository {

    private IDateTimeService            $dateTimeService;
    private IOrganizationUserRepository $organizationUserRepository;
    private IBackend                    $backend;
    private ILogger                     $logger;

    public function __construct(
        IOrganizationUserRepository $organizationUserRepository
        , IDateTimeService          $dateTimeService
        , IBackend                  $backend
        , ILogger                   $logger
    ) {
        $this->dateTimeService            = $dateTimeService;
        $this->organizationUserRepository = $organizationUserRepository;
        $this->backend                    = $backend;
        $this->logger                     = $logger;
    }

    /**
     * @return ArrayList<IOrganization>
     * @throws Exception
     */
    public function getAll(): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'o.id'
                , 'o.name'
                , 'o.password'
                , 'o.create_ts'
                , 'o.active_ts'
            ]
        )
            ->from('`organization`', 'o');
        $result       = $queryBuilder->executeQuery();

        foreach ($result->fetchAllAssociative() as $row) {
            $organization = new Organization();
            $organization->setId((int) $row['id']);
            $organization->setName((string) $row['name']);
            $organization->setPassword((string) $row['password']);
            $organization->setActiveTs(
                null === $row['active_ts']
                    ? null
                    : $this->dateTimeService->fromFormat($row['active_ts'])
            );
            $organization->setCreateTs(
                $this->dateTimeService->fromFormat($row['create_ts'])
            );
            $organization = $this->organizationUserRepository->getByOrganization($organization);
            $list->add($organization);
        }
        return $list;
    }

    /**
     * @return ArrayList<IOrganization>
     * @throws Exception
     */
    public function getAllForUser(IUser $user): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'o.`id`'
                , 'o.`name`'
                , 'o.`password`'
                , 'o.`create_ts`'
                , 'o.`active_ts`'
            ]
        )
            ->from('`organization`', 'o')
            ->innerJoin('o', '`user_organization`', 'uo', 'o.id = uo.`organization_id`')
            ->where('uo.`user_id` = ?')
            ->setParameter(0, $user->getId());

        $result = $queryBuilder->executeQuery();

        foreach ($result->fetchAllAssociative() as $row) {
            $organization = new Organization();
            $organization->setId((int) $row['id']);
            $organization->setName((string) $row['name']);
            $organization->setPassword((string) $row['password']);
            $organization->setActiveTs(
                null === $row['active_ts']
                    ? null
                    : $this->dateTimeService->fromFormat($row['active_ts'])
            );
            $organization->setCreateTs(
                $this->dateTimeService->fromFormat($row['create_ts'])
            );
            $organization = $this->organizationUserRepository->getByOrganization($organization);
            $list->add($organization);
        }
        return $list;
    }

    /**
     * @param IOrganization $organization
     * @return IOrganization
     * @throws OrganizationNotUpdatedException
     */
    public function update(IOrganization $organization): IOrganization {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder = $queryBuilder->update('organization')
                ->set('name', '?')
                ->set('active_ts', '?')
                ->where('id = ?')
                ->setParameter(0, $organization->getName())
                ->setParameter(1,
                    null === $organization->getActiveTs()
                        ? null
                        : $this->dateTimeService->toYMDHIS($organization->getActiveTs())
                )
                ->setParameter(2, $organization->getId());
            $rowCount     = $queryBuilder->executeStatement();

            if (0 === $rowCount) {
                throw new OrganizationNotUpdatedException('no rows updated');
            }

            return $organization;
        } catch (Exception $exception) {
            $this->logger->error('error while updating organization', ['exception' => $exception]);
            throw new OrganizationNotUpdatedException();
        }
    }

    public function insert(IOrganization $organization): IOrganization {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('organization')
            ->values(
                [
                    'name'        => '?'
                    , 'password'  => '?'
                    , 'create_ts' => '?'
                    , 'active_ts' => '?'
                ]
            )
            ->setParameter(0, $organization->getName())
            ->setParameter(1, $organization->getPassword())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS(
                    $organization->getCreateTs()
                )
            )
            ->setParameter(3,
                null === $organization->getActiveTs()
                    ? null
                    : $this->dateTimeService->toYMDHIS(
                    $organization->getActiveTs()
                )
            )
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new SettingsException();
        }
        $organization->setId((int) $lastInsertId);
        return $organization;
    }

    /**
     * @param string $name
     * @return IOrganization
     * @throws Exception
     * @throws OrganizationNotFoundException
     */
    public function getByName(string $name): IOrganization {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'password'
                , 'create_ts'
                , 'active_ts'
            ]
        )
            ->from('organization')
            ->where('name = ?')
            ->setParameter(0, $name);
        $result       = $queryBuilder->executeQuery();
        $rows         = $result->fetchAllAssociative();
        $rowCount     = count($rows);

        if (0 === $rowCount) {
            throw new OrganizationNotFoundException();
        }

        $organization = new Organization();
        $organization->setId((int) $rows[0]['id']);
        $organization->setName((string) $rows[0]['name']);
        $organization->setPassword((string) $rows[0]['password']);
        $organization->setActiveTs(
            null === $rows[0]['active_ts']
                ? null
                : $this->dateTimeService->fromFormat($rows[0]['active_ts'])
        );
        $organization->setCreateTs(
            $this->dateTimeService->fromFormat($rows[0]['create_ts'])
        );
        return $this->organizationUserRepository->getByOrganization($organization);
    }

    public function get(int $id): ?IOrganization {
        $organization = null;
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'password'
                , 'create_ts'
                , 'active_ts'
            ]
        )
            ->from('organization')
            ->where('id = ?')
            ->setParameter(0, $id);
        $result       = $queryBuilder->executeQuery();


        foreach ($result->fetchAllAssociative() as $row) {
            $organization = new Organization();
            $organization->setId((int) $row['id']);
            $organization->setName((string) $row['name']);
            $organization->setPassword((string) $row['password']);
            $organization->setActiveTs(
                null === $row['active_ts']
                    ? null
                    : $this->dateTimeService->fromFormat($row['active_ts'])
            );
            $organization->setCreateTs(
                $this->dateTimeService->fromFormat($row['create_ts'])
            );
            $organization = $this->organizationUserRepository->getByOrganization($organization);
        }
        return $organization;
    }

    public function remove(IOrganization $organization): IOrganization {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('organization')
                ->where('id = ?')
                ->setParameter(0, $organization->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error while deleting', ['exception' => $exception]);
            throw new OrganizationNotDeletedException();
        }
        return $organization;
    }

}