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

namespace Keestash\Core\DTO\File\Upload;

use KSP\Core\DTO\File\Upload\IFile;

class File implements IFile {

    private string $name;
    private string $type;
    private string $tmpName;
    private int    $error;
    private int    $size;

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTmpName(): string {
        return $this->tmpName;
    }

    /**
     * @param string $tmpName
     */
    public function setTmpName(string $tmpName): void {
        $this->tmpName = $tmpName;
    }

    /**
     * @return int
     */
    public function getError(): int {
        return $this->error;
    }

    /**
     * @param int $error
     */
    public function setError(int $error): void {
        $this->error = $error;
    }

    /**
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void {
        $this->size = $size;
    }

}
