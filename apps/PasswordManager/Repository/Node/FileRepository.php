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
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Common\Exception\NodeNotFoundException;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\File\FileList;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Repository\File\FileRepository as CoreFileRepository;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Entity\Node\Node;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Service\HTTP\IJWTService;
use Psr\Log\LoggerInterface;

class FileRepository {

    public function __construct(
        private readonly IBackend             $backend
        , private readonly NodeRepository     $nodeRepository
        , private readonly IDateTimeService   $dateTimeService
        , private readonly CoreFileRepository $fileRepository
        , private readonly LoggerInterface    $logger
        , private readonly IJWTService        $jwtService
    ) {
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
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
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
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();
        if (false === is_numeric($lastInsertId)) return false;
        return true;
    }

    public function getFilesPerNode(Node $node, ?string $type = null): ArrayList {
        $list = new ArrayList();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'pnf.`id`'
                , 'pnf.`file_id`'
                , 'pnf.`node_id`'
                , 'pnf.`type`'
                , 'pnf.`create_ts`'
                , 'f.`extension`'
            ]
        )
            ->from('`pwm_node_file`', 'pnf')
            ->join('pnf', '`file`', 'f', 'pnf.file_id = f.id')
            ->where('node_id = ?');

        $queryBuilder = $queryBuilder
            ->setParameter(0, $node->getId());

        if (null !== $type) {
            $queryBuilder = $queryBuilder->andWhere('type = ?');
            $queryBuilder = $queryBuilder->setParameter(1, $type);
        }

        $result = $queryBuilder->executeQuery();
        $rows   = $result->fetchAllNumeric();

        foreach ($rows as $row) {
            $nodeFile = new NodeFile();
            $nodeFile->setFile($this->fileRepository->get((int) $row[1]));
            $nodeFile->setNode($this->nodeRepository->getNode((int) $row[2], 0, 1));
            $nodeFile->setType($row[3]);
            $nodeFile->setCreateTs(
                $this->dateTimeService->fromFormat($row[4])
            );
            $nodeFile->setJwt(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_ASSET
                        , $row[5]
                    )
                )
            );
            $list->add($nodeFile);
        }

        return $list;

    }

    public function getNode(IFile $file): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'node_id'
            ]
        )
            ->from('pwm_node_file')
            ->where('file_id = ?')
            ->setParameter(0, $file->getId());

        $result   = $queryBuilder->executeQuery();
        $rows     = $result->fetchAllNumeric();
        $rowCount = count($rows);

        if (0 === $rowCount) {
            throw new NodeNotFoundException();
        }

        return $this->nodeRepository->getNode(
            (int) $rows[0][0]
            , 0
            , 0
        );
    }

    public function removeByFile(IFile $file): bool {
        // TODO notify node for update
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('pwm_node_file')
                ->where('file_id = ?')
                ->setParameter(0, $file->getId())
                ->executeStatement() !== 0;
    }

}
