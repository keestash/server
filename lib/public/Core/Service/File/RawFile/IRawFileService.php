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

namespace KSP\Core\Service\File\RawFile;

use Keestash\Exception\File\FileNotCreatedException;
use Keestash\Exception\File\FileNotExistsException;
use Keestash\Exception\File\FileNotFoundException;
use Keestash\Exception\IndexOutOfBoundsException;
use Keestash\Exception\KeestashException;
use Keestash\Exception\UnknownExtensionException;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;

interface IRawFileService {

    /**
     * @param string $path
     * @return string
     * @throws KeestashException
     */
    public function getMimeType(string $path): string;

    /**
     * @param string $path
     * @return array
     * @throws IndexOutOfBoundsException
     * @throws UnknownExtensionException
     */
    public function getFileExtensions(string $path): array;

    /**
     * @param string $path
     * @param bool   $strict
     * @return IUniformResourceIdentifier
     * @throws FileNotExistsException
     */
    public function stringToUri(string $path, bool $strict = true): IUniformResourceIdentifier;

    /**
     * @param string $path
     * @param bool   $rawBase64
     * @return string
     * @throws FileNotCreatedException
     * @throws FileNotFoundException
     */
    public function stringToBase64(string $path, bool $rawBase64 = false): string;


}