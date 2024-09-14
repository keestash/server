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

namespace Keestash\Core\Service\File\Icon;

use Keestash;
use KSP\Core\DTO\File\Icon\IICon;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\Service\File\Icon\IIconService;

class IconService implements IIconService {

    /**
     * @param string $extension
     *
     * @return string
     */
    #[\Override]
    public function getIconForExtension(string $extension): string {
        $icon = null;

        $icon = match ($extension) {
            IExtension::JPEG, IExtension::JPG => IICon::JPEG,
            IExtension::PNG => IICon::PNG,
            IExtension::PDF => IICon::PDF,
            IExtension::DOCX, IExtension::DOC => IICon::DOC,
            IExtension::TEXT => IICon::TXT,
            default => IICon::PDF,
        };

        return $icon;

    }

}
