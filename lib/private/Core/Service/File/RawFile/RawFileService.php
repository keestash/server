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
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use xobotyi\MimeType;

class RawFileService {

    public function getMimeType(string $path): ?string {
        $path = realpath($path);
        if (false === $path) return null;

        $f = finfo_open();
        return finfo_buffer($f, file_get_contents($path), FILEINFO_MIME_TYPE);
    }

    public function getFileExtensions(string $path): ?array {
        $mimeType = $this->getMimeType($path);
        if (null === $mimeType) return null;
        return MimeType::getExtensions($mimeType);
    }

    public function stringToUri(string $path, bool $strict = true): ?IUniformResourceIdentifier {

        if (true === $strict) {
            $path = realpath($path);
            if (false === $path) return null;
        }

        $uri = new URI();
        $uri->setIdentifier($path);
        return $uri;
    }

    public function stringToBase64(string $path, bool $rawBase64 = false): ?string {

        $content = @file_get_contents($path);

        if (false === $content) return null;
        $tempNae = tempnam(sys_get_temp_dir(), "pp_");
        $put     = file_put_contents($tempNae, $content);
        if (false === $put) return null;

        $base64 = $imgData = base64_encode($content);

        if (false === $rawBase64) {
            $base64 = 'data: ' . mime_content_type($tempNae) . ';base64, ' . $imgData;
        }

        return $base64;
    }

}