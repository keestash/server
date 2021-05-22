<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

use DateTime;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\File\FileList;
use Keestash\Core\Repository\AbstractRepository;
use Keestash\Core\Repository\File\FileRepository as CoreFileRepository;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Entity\Node;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\File\IFile;
use KSP\Core\ILogger\ILogger;

class FileRepository extends AbstractRepository {

    private NodeRepository     $nodeRepository;
    private DateTimeService    $dateTimeService;
    private CoreFileRepository $fileRepository;
    private ILogger            $logger;

    public function __construct(
        IBackend $backend
        , NodeRepository $nodeRepository
        , DateTimeService $dateTimeService
        , CoreFileRepository $fileRepository
        , ILogger $logger
    ) {
        parent::__construct($backend);

        $this->nodeRepository  = $nodeRepository;
        $this->dateTimeService = $dateTimeService;
        $this->fileRepository  = $fileRepository;
        $this->logger          = $logger;
    }

    public function connectFilesToNode(FileList $fileList): bool {
        $connectedAll = false;

        foreach ($fileList as $file) {
            $connected    = $this->connectFileToNode($file);
            $connectedAll = $connectedAll || $connected;
        }

        return $connectedAll;
    }

    public function connectFileToNode(NodeFile $nodeFile): bool {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insert('pwm_node_file')
            ->values(
                [
                    'node_id'     => '?'
                    , 'file_id'   => '?'
                    , 'type'      => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $nodeFile->getNode()->getId())
            ->setParameter(1, $nodeFile->getFile()->getId())
            ->setParameter(2, $nodeFile->getType())
            ->setParameter(
                3
                , $this->dateTimeService->toYMDHIS(new DateTime())
            )
            ->execute();

        $lastInsertId = $this->getLastInsertId();
        if (null === $lastInsertId) return false;
        return true;
    }

    public function getFilesPerNode(Node $node, ?string $type = null): ArrayList {
        $list = new ArrayList();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'file_id'
                , 'node_id'
                , 'type'
                , 'create_ts'
            ]
        )
            ->from('pwm_node_file')
            ->where('node_id = ?');

        $queryBuilder = $queryBuilder
            ->setParameter(0, $node->getId());

        if (null !== $type) {
            $queryBuilder = $queryBuilder->andWhere('type = ?');
            $queryBuilder = $queryBuilder->setParameter(1, $type);
        }

        $rows = $queryBuilder->execute();

        foreach ($rows as $row) {
            $nodeFile = new NodeFile();
            $nodeFile->setFile($this->fileRepository->get((int) $row['file_id']));
            $nodeFile->setNode($this->nodeRepository->getNode((int) $row['node_id'], 0, 1));
            $nodeFile->setType($row['type']);
            $nodeFile->setCreateTs(
                $this->dateTimeService->fromFormat($row['create_ts'])
            );
            $list->add($nodeFile);
        }

        return $list;

    }

    public function getNode(IFile $file): ?Node {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'node_id'
            ]
        )
            ->from('pwm_node_file')
            ->where('file_id = ?')
            ->setParameter(0, $file->getId());

        $result = $queryBuilder->execute();
        $row    = $result->fetchAllNumeric()[0];
        $nodeId = (int) $row[0];

        return $this->nodeRepository->getNode((int) $nodeId, 0, 0);
    }

    public function removeByFile(IFile $file): bool {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete('pwm_node_file')
                ->where('file_id = ?')
                ->setParameter(0, $file->getId())
                ->execute() !== 0;
    }

}
