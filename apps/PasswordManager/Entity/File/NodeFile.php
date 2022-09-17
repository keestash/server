<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\PasswordManager\Entity\File;

use DateTimeInterface;
use Exception;
use KSA\PasswordManager\Entity\Node\Node;
use KSP\Core\DTO\Entity\JWT;
use KSP\Core\DTO\File\IFile;

class NodeFile implements JWT {

    public const FILE_TYPE_AVATAR     = "avatar.type.file";
    public const FILE_TYPE_ATTACHMENT = "attachment.type.file";

    private IFile             $file;
    private Node              $node;
    private string            $type;
    private DateTimeInterface $createTs;
    private ?string           $jwt = null;

    private array $types = [
        NodeFile::FILE_TYPE_ATTACHMENT
        , NodeFile::FILE_TYPE_AVATAR
    ];

    /**
     * @return IFile
     */
    public function getFile(): IFile {
        return $this->file;
    }

    /**
     * @param IFile $file
     */
    public function setFile(IFile $file): void {
        $this->file = $file;
    }

    /**
     * @return Node
     */
    public function getNode(): Node {
        return $this->node;
    }

    /**
     * @param Node $node
     */
    public function setNode(Node $node): void {
        $this->node = $node;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @throws Exception
     */
    public function setType(string $type): void {
        if (false === in_array($type, $this->types)) {
            throw new Exception();
        }
        $this->type = $type;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @param DateTimeInterface $createTs
     */
    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * @return string|null
     */
    public function getJwt(): ?string {
        return $this->jwt;
    }

    /**
     * @param string|null $jwt
     */
    public function setJwt(?string $jwt): void {
        $this->jwt = $jwt;
    }

    public function jsonSerialize(): array {
        return [
            'file'          => $this->getFile()
            , 'node'        => $this->getNode()
            , 'type'        => $this->getType()
            , 'create_ts'   => $this->getCreateTs()
            , 'valid_types' => $this->types
            , 'jwt'         => $this->getJwt()
        ];
    }

}
