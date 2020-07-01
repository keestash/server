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

use DateTime;
use KSP\Core\DTO\File\IJsonFile;
use KSP\Core\DTO\User\IJsonUser;

class File implements IJsonFile {

    private $id            = null;
    private $name          = null;
    private $directory     = null;
    private $temporaryPath = null;
    private $mimeType      = null;
    private $hash          = null;
    private $extension     = null;
    private $size          = null;
    private $content       = null;
    private $owner         = null;
    private $createTs      = null;

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setTemporaryPath(string $temporaryPath): void {
        $this->temporaryPath = $temporaryPath;
    }

    public function getTemporaryPath(): ?string {
        return $this->temporaryPath;
    }

    public function setDirectory(string $directory): void {
        $this->directory = $directory;
    }

    public function getDirectory(): string {
        return $this->directory;
    }

    public function getFullPath(): string {
        $name      = $this->getName();
        $dir       = $this->getDirectory();
        $extension = $this->getExtension();

        $path      = "$dir/$name.$extension";
        $path      = str_replace("//", "/", $path);
        return $path;

    }

    public function setMimeType(string $mimeType): void {
        $this->mimeType = $mimeType;
    }

    public function getMimeType(): string {
        return $this->mimeType;
    }

    public function setHash(string $hash): void {
        $this->hash = $hash;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function setExtension(string $extension): void {
        $this->extension = $extension;
    }

    public function getExtension(): string {
        return $this->extension;
    }

    public function setSize(int $size): void {
        $this->size = $size;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function setOwner(IJsonUser $owner): void {
        $this->owner = $owner;
    }

    public function getOwner(): IJsonUser {
        return $this->owner;
    }

    public function setContent(?string $content): void {
        $this->content = $content;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function setCreateTs(DateTime $createTs): void {
        $this->createTs = $createTs;
    }

    public function getCreateTs(): DateTime {
        return $this->createTs;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            "id"          => $this->getId()
            , "name"      => $this->getName()
            , "tmp_path"  => $this->getTemporaryPath()
            , "directory" => $this->getDirectory()
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
