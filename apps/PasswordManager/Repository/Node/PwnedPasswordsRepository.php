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
use KSA\PasswordManager\Entity\Node\Pwned\Passwords;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;

class PwnedPasswordsRepository {

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

    public function replace(Passwords $passwords): Passwords {
        $passwords    = $this->remove($passwords);
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('pwm_pwned_passwords')
            ->values(
                [
                    'node_id'     => '?'
                    , 'severity'  => '?'
                    , 'create_ts' => '?'
                    , 'update_ts' => '?'
                ]
            )
            ->setParameter(0, $passwords->getNode()->getId())
            ->setParameter(1, $passwords->getSeverity())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS(
                    $passwords->getCreateTs()
                )
            )
            ->setParameter(3,
                null !== $passwords->getUpdateTs()
                    ? $this->dateTimeService->toYMDHIS(
                    $passwords->getCreateTs()
                )
                    : null
            )
            ->executeStatement();

        return $passwords;
    }

    public function remove(Passwords $pwned): Passwords {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->delete(
            'pwm_pwned_passwords'
        )
            ->where('node_id = ?')
            ->setParameter(0, $pwned->getNode()->getId())
            ->executeStatement();
        return $pwned;
    }

    public function getPwnedByNode(Node $node, int $minimumSeverity = 0): HashTable {
        $result = new HashTable();

        if ($node instanceof Credential) {
            $this->_getPwnedByNode($node, $minimumSeverity, $result);
            return $result;
        }

        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {
            $this->_getPwnedByNode($edge->getNode(), $minimumSeverity, $result);
        }
        return $result;
    }

    public function getOlderThan(DateTimeInterface $reference): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $pwned        = $queryBuilder
            ->select(
                [
                    'id'
                    , 'node_id'
                    , 'severity'
                    , 'create_ts'
                    , 'update_ts'
                ]
            )
            ->from('pwm_pwned_passwords')
            ->andWhere('update_ts < ?')
            ->orWhere('update_ts IS NULL')
            ->setParameter(
                0
                , $this->dateTimeService->toYMDHIS($reference)
            );

        $pwned = $pwned->executeQuery()
            ->fetchAllAssociative();
        foreach ($pwned as $row) {
            $pwned = new Passwords(
                $this->nodeRepository->getNode((int) $row['node_id'], 0, 0)
                , (int) $row['severity']
                , $this->dateTimeService->fromFormat($row['create_ts'])
                , null !== $row['update_ts']
                ? $this->dateTimeService->fromFormat($row['update_ts'])
                : null
            );

            $list->add($pwned);
        }
        return $list;
    }

    private function _getPwnedByNode(Node $node, int $minimumSeverity, HashTable &$hashTable): void {

        if ($node instanceof Folder) {
            foreach ($node->getEdges() as $edge) {
                $this->_getPwnedByNode($edge->getNode(), $minimumSeverity, $hashTable);
            }
            return;
        }

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $pwned        = $queryBuilder
            ->select(
                [
                    'id'
                    , 'node_id'
                    , 'severity'
                    , 'create_ts'
                    , 'update_ts'
                ]
            )
            ->from('pwm_pwned_passwords')
            ->where('node_id = ?')
            ->andWhere('severity > ?')
            ->setParameter(0, $node->getId())
            ->setParameter(1, $minimumSeverity);
        $pwned        = $pwned->executeQuery()
            ->fetchAllAssociative();

        foreach ($pwned as $row) {
            $pwned = new Passwords(
                $this->nodeRepository->getNode((int) $row['node_id'], 0, 0)
                , (int) $row['severity']
                , $this->dateTimeService->fromFormat($row['create_ts'])
                , null !== $row['update_ts']
                ? $this->dateTimeService->fromFormat($row['update_ts'])
                : null

            );

            $hashTable->put($pwned->getNode()->getId(), $pwned);
        }
    }

}