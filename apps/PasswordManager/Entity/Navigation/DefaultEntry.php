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

namespace KSA\PasswordManager\Entity\Navigation;

use Keestash\View\Navigation\App\Entry;

class DefaultEntry extends Entry {

    public const DEFAULT_ENTRY_HOME              = "root";
    public const DEFAULT_ENTRY_FAVORITE          = "favorite";
    public const DEFAULT_ENTRY_RECENTLY_MODIFIED = "modified.recently";
    public const DEFAULT_ENTRY_SHARED_WITH_ME    = "me.with.shared";

}
