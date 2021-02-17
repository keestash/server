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

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Organization\Organization;
use Keestash\Core\Repository\AbstractRepository;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Organization\IOrganization;

class OrganizationRepository extends AbstractRepository implements IOrganizationRepository {

    private IDateTimeService $dateTimeService;

    public function __construct(
        IDateTimeService $dateTimeService
        , IBackend $backend
    ) {
        parent::__construct($backend);
        $this->dateTimeService = $dateTimeService;
    }

    public function getAll(): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'create_ts'
                , 'active_ts'
            ]
        )
            ->from('organization');
        $rows         = $queryBuilder->execute();

        foreach ($rows as $row) {
            $organization = new Organization();
            $organization->setId((int) $row['id']);
            $organization->setName((string) $row['name']);
            $organization->setMemberCount(0);
            $organization->setActiveTs(
                null === $row['active_ts']
                    ? null
                    : $this->dateTimeService->fromFormat($row['active_ts'])
            );
            $organization->setCreateTs(
                $this->dateTimeService->fromFormat($row['create_ts'])
            );
            $list->add($organization);
        }
        return $list;
    }

    public function update(IOrganization $organization): IOrganization {
        $queryBuilder = $this->getQueryBuilder();
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
        $rowCount     = $queryBuilder->execute();

        if (0 === $rowCount) {
            throw new GeneralApiException('no rows updated');
        }

        return $organization;
    }

    public function insert(IOrganization $organization): IOrganization {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insert('organization')
            ->values(
                [
                    'name'        => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $organization->getName())
            ->setParameter(1,
                $this->dateTimeService->toYMDHIS(
                    $organization->getCreateTs()
                )
            )
            ->execute();

        $lastInsertId = $this->getDoctrineLastInsertId();

        if (null === $lastInsertId) {
            throw new GeneralApiException();
        }
        $organization->setId((int) $lastInsertId);
        return $organization;
    }

    public function get(int $id): ?IOrganization {
        $organization = null;
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'create_ts'
                , 'active_ts'
            ]
        )
            ->from('organization')
            ->where('id = ?')
            ->setParameter(0, $id);
        $rows         = $queryBuilder->execute();

        foreach ($rows as $row) {
            $organization = new Organization();
            $organization->setId((int) $row['id']);
            $organization->setName((string) $row['name']);
            $organization->setMemberCount(0);
            $organization->setActiveTs(
                null === $row['active_ts']
                    ? null
                    : $this->dateTimeService->fromFormat($row['active_ts'])
            );
            $organization->setCreateTs(
                $this->dateTimeService->fromFormat($row['create_ts'])
            );
        }
        return $organization;
    }

}