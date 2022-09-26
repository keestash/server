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

namespace KSA\PasswordManager\Repository\Node;

use DateTimeInterface;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Entity\Node\Pwned\Breaches;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;

class PwnedBreachesRepository {

    private IBackend         $backend;
    private ILogger          $logger;
    private IDateTimeService $dateTimeService;
    private NodeRepository   $nodeRepository;

    public function __construct(
        IBackend           $backend
        , ILogger          $logger
        , IDateTimeService $dateTimeService
        , NodeRepository   $nodeRepository
    ) {
        $this->backend         = $backend;
        $this->logger          = $logger;
        $this->dateTimeService = $dateTimeService;
        $this->nodeRepository  = $nodeRepository;
    }

    public function replace(Breaches $breaches): Breaches {
        $breaches     = $this->remove($breaches);
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('pwm_pwned_breaches')
            ->values(
                [
                    'node_id'     => '?'
                    , 'hibp_data' => '?'
                    , 'create_ts' => '?'
                    , 'update_ts' => '?'
                ]
            )
            ->setParameter(0, $breaches->getNode()->getId())
            ->setParameter(1,
                null !== $breaches->getHibpData()
                    ? json_encode($breaches->getHibpData())
                    : null
            )
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS(
                    $breaches->getCreateTs()
                )
            )
            ->setParameter(3,
                null !== $breaches->getUpdateTs()
                    ? $this->dateTimeService->toYMDHIS(
                    $breaches->getUpdateTs()
                )
                    : null
            )
            ->executeStatement();

        return $breaches;
    }

    public function remove(Breaches $pwned): Breaches {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->delete(
            'pwm_pwned_breaches'
        )
            ->where('node_id = ?')
            ->setParameter(0, $pwned->getNode()->getId())
            ->executeStatement();
        return $pwned;
    }


    public function getOlderThan(DateTimeInterface $reference): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $pwned        = $queryBuilder
            ->select(
                [
                    'id'
                    , 'node_id'
                    , 'hibp_data'
                    , 'create_ts'
                    , 'update_ts'
                ]
            )
            ->from('pwm_pwned_breaches')
            ->andWhere('update_ts < ?')
            ->orWhere('update_ts IS NULL')
            ->setParameter(
                0
                , $this->dateTimeService->toYMDHIS($reference)
            );

        $pwned = $pwned->executeQuery()
            ->fetchAllAssociative();
        foreach ($pwned as $row) {
            $pwned = new Breaches(
                $this->nodeRepository->getNode((int) $row['node_id'], 0, 0)
                , null !== $row['hibp_data']
                ? json_decode($row['hibp_data'], true)
                : null
                , $this->dateTimeService->fromFormat($row['create_ts'])
                , null !== $row['update_ts']
                ? $this->dateTimeService->fromFormat($row['update_ts'])
                : null
            );

            $list->add($pwned);
        }
        return $list;
    }

    public function getPwnedByNode(Node $node): HashTable {
        $result = new HashTable();
        if ($node instanceof Credential) {
            $this->_getPwnedByNode($node, $result);
            return $result;
        }
        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {
            $this->_getPwnedByNode($edge->getNode(), $result);
        }
        return $result;
    }

    private function _getPwnedByNode(Node $node, HashTable &$hashTable): void {

        if ($node instanceof Folder) {
            foreach ($node->getEdges() as $edge) {
                $this->_getPwnedByNode($edge->getNode(), $hashTable);
            }
            return;
        }
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $pwned        = $queryBuilder
            ->select(
                [
                    'id'
                    , 'node_id'
                    , 'hibp_data'
                    , 'create_ts'
                    , 'update_ts'
                ]
            )
            ->from('pwm_pwned_breaches')
            ->where('node_id = ?')
            ->setParameter(0, $node->getId());

        $pwned = $pwned->executeQuery()
            ->fetchAllAssociative();

        foreach ($pwned as $row) {
            $pwned = new Breaches(
                $this->nodeRepository->getNode((int) $row['node_id'], 0, 0)
                , null === $row['hibp_data']
                ? null
                : json_decode($row['hibp_data'], true)
                , $this->dateTimeService->fromFormat($row['create_ts'])
                , null !== $row['update_ts']
                ? $this->dateTimeService->fromFormat($row['update_ts'])
                : null
            );

            $hashTable->put($pwned->getNode()->getId(), $pwned);
        }
    }

}