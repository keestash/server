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

namespace KSA\PasswordManager\Repository\Node\Credential\AdditionalData;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\AdditionalData;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\Value;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\Node\NotFoundException;
use KSP\Core\Backend\IBackend;

class AdditionalDataRepository {

    public function __construct(
        private readonly IBackend           $backend
        , private readonly IDateTimeService $dateTimeService
    ) {
    }

    public function add(AdditionalData $additionalData): void {
        $this->backend->startTransaction();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder
            ->insert('`pwm_additional_data`')
            ->values(
                [
                    '`id`'          => '?'
                    , '`key`'       => '?'
                    , '`value`'     => '?'
                    , '`node_id`'   => '?'
                    , '`create_ts`' => '?'
                ]
            )
            ->setParameter(0, $additionalData->getId())
            ->setParameter(1, $additionalData->getKey())
            ->setParameter(2, $additionalData->getValue()->getEncrypted())
            ->setParameter(3, $additionalData->getNodeId())
            ->setParameter(4, $this->dateTimeService->toYMDHIS($additionalData->getCreateTs()));

        $queryBuilder->executeStatement();
        $this->backend->endTransaction();
    }

    public function getByNode(Node $node): ArrayList {
        $list = new ArrayList();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'pad.`id`'
                , 'pad.`key`'
                , 'pad.`value`'
                , 'pad.`node_id`'
                , 'pad.`create_ts`'
            ]
        )
            ->from('`pwm_additional_data`', 'pad')
            ->where('node_id = ?');

        $result = $queryBuilder
            ->setParameter(0, $node->getId())
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        foreach ($rows as $row) {
            $list->add(
                new AdditionalData(
                    $row['id'],
                    $row['key'],
                    new Value(
                        encrypted: $row['value']
                    ),
                    $node->getId(),
                    $this->dateTimeService->fromFormat((string) $row['create_ts'])
                )
            );
        }

        return $list;
    }

    public function getById(string $id): AdditionalData {

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'pad.`id`'
                , 'pad.`key`'
                , 'pad.`value`'
                , 'pad.`node_id`'
                , 'pad.`create_ts`'
            ]
        )
            ->from('`pwm_additional_data`', 'pad')
            ->where('id = ?');

        $result = $queryBuilder
            ->setParameter(0, $id)
            ->executeQuery();

        $rows     = $result->fetchAllAssociative();
        $rowCount = count($rows);

        if (0 === $rowCount) {
            throw new NotFoundException();
        }

        return new AdditionalData(
            $rows[0]['id'],
            $rows[0]['key'],
            new Value(
                encrypted: $rows[0]['value']
            ),
            $rows[0]['node_id'],
            $this->dateTimeService->fromFormat((string) $rows[0]['create_ts'])
        );
    }

    public function remove(AdditionalData $additionalData): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->delete('`pwm_additional_data`')
            ->where('id = ?')
            ->setParameter(0, $additionalData->getId())
            ->executeStatement();
    }

}
