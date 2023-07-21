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

namespace Keestash\Core\DTO\File;

use DateTimeInterface;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;

class File implements IFile {

    private int               $id            = 0;
    private string            $name;
    private string            $directory;
    private ?string           $temporaryPath = null;
    private string            $mimeType;
    private string            $hash;
    private string            $extension;
    private int               $size;
    private ?string           $content       = null;
    private IUser             $owner;
    private DateTimeInterface $createTs;

    public function getFullPath(): string {
        $name      = $this->getName();
        $dir       = $this->getDirectory();
        $extension = $this->getExtension();

        if ("" === $extension) {
            $path = "$dir/$name";
        } else {
            $path = "$dir/$name.$extension";
        }
        return str_replace("//", "/", $path);

    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getDirectory(): string {
        return $this->directory;
    }

    public function setDirectory(string $directory): void {
        $this->directory = $directory;
    }

    public function getExtension(): string {
        return $this->extension;
    }

    public function setExtension(string $extension): void {
        $this->extension = $extension;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getTemporaryPath(): ?string {
        return $this->temporaryPath;
    }

    public function setTemporaryPath(string $temporaryPath): void {
        $this->temporaryPath = $temporaryPath;
    }

    public function getMimeType(): string {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void {
        $this->mimeType = $mimeType;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function setHash(string $hash): void {
        $this->hash = $hash;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function setSize(int $size): void {
        $this->size = $size;
    }

    public function getOwner(): IUser {
        return $this->owner;
    }

    public function setOwner(IUser $owner): void {
        $this->owner = $owner;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function setContent(?string $content): void {
        $this->content = $content;
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return [
            "id"          => $this->getId()
            , "name"      => $this->getName()
            , "mime_type" => $this->getMimeType()
            , "hash"      => $this->getHash()
            , "extension" => $this->getExtension()
            , "size"      => $this->getSize()
            , "owner"     => $this->getOwner()
            , "content"   => $this->getContent()
            , "create_ts" => $this->getCreateTs()
        ];
    }
}
