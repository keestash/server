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

namespace Keestash\Core\Service\File\RawFile;

use Keestash\Core\DTO\URI\URI;
use Keestash\Exception\File\FileNotCreatedException;
use Keestash\Exception\File\FileNotExistsException;
use Keestash\Exception\File\FileNotFoundException;
use Keestash\Exception\IndexOutOfBoundsException;
use Keestash\Exception\KeestashException;
use Keestash\Exception\UnknownExtensionException;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\Service\File\Mime\IMimeTypeService;
use KSP\Core\Service\File\RawFile\IRawFileService;

class RawFileService implements IRawFileService {

    private IMimeTypeService $mimeTypeService;

    public function __construct(IMimeTypeService $mimeTypeService) {
        $this->mimeTypeService = $mimeTypeService;
    }

    /**
     * @param string $path
     * @return string
     * @throws KeestashException
     */
    public function getMimeType(string $path): string {
        $path = realpath($path);
        if (false === $path) {
            throw new KeestashException();
        }

        $f = finfo_open();

        if (false === $f) {
            throw new KeestashException(
                sprintf('file info could not opened in %s', $path)
            );
        }

        $buffer = finfo_buffer($f, (string) file_get_contents($path), FILEINFO_MIME_TYPE);

        if (false === $buffer) {
            throw new KeestashException();
        }
        return $buffer;
    }

    /**
     * @param string $path
     * @return array
     * @throws IndexOutOfBoundsException
     * @throws UnknownExtensionException
     */
    public function getFileExtensions(string $path): array {
        try {
            $mimeType = $this->getMimeType($path);
        } catch (KeestashException $exception) {
            return [];
        }
        return $this->mimeTypeService->getExtension($mimeType);
    }

    /**
     * @param string $path
     * @param bool   $strict
     * @return IUniformResourceIdentifier
     * @throws FileNotExistsException
     */
    public function stringToUri(string $path, bool $strict = true): IUniformResourceIdentifier {

        if (true === $strict) {
            $path = realpath($path);
            if (false === $path) {
                throw new FileNotExistsException();
            }
        }

        $uri = new URI();
        $uri->setIdentifier($path);
        return $uri;
    }

    /**
     * @param string $path
     * @param bool   $rawBase64
     * @return string
     * @throws FileNotCreatedException
     * @throws FileNotFoundException
     */
    public function stringToBase64(string $path, bool $rawBase64 = false): string {

        $content = @file_get_contents($path);

        if (false === $content) {
            throw new FileNotFoundException();
        }

        $tempName = (string) tempnam(sys_get_temp_dir(), "pp_");

        if (false === is_file($tempName)) {
            throw new FileNotFoundException();
        }

        $put = file_put_contents($tempName, $content);
        if (false === $put) {
            throw new FileNotCreatedException();
        }

        $base64 = $imgData = base64_encode($content);

        if (false === $rawBase64) {
            $base64 = 'data: ' . mime_content_type($tempName) . ';base64, ' . $imgData;
        }

        return $base64;
    }

}
