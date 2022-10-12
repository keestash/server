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

namespace Keestash\Core\Service\File\Mime;

use Keestash\Exception\IndexOutOfBoundsException;
use Keestash\Exception\UnknownExtensionException;
use KSP\Core\Service\File\Mime\IMimeTypeService;

class MimeTypeService implements IMimeTypeService {

    /**
     * @param string $mimeType
     * @return array
     * @throws IndexOutOfBoundsException
     * @throws UnknownExtensionException
     */
    public function getExtension(string $mimeType): array {
        $position = strpos($mimeType, "/");

        if (false === $position) {
            throw new IndexOutOfBoundsException();
        }

        $group = substr($mimeType, 0, $position);
        $type  = substr($mimeType, $position + 1);

        $extension = IMimeTypeService::MIMES[$group][$type] ?? null;
        if (null === $extension) {
            throw new UnknownExtensionException();
        }

        return $extension;
    }

}