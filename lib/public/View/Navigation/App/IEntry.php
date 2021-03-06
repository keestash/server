<?php
declare(strict_types=1);
/**
 * Keestash
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

namespace KSP\View\Navigation\App;

use KSP\Core\DTO\Entity\IObject;

/**
 * Interface IEntry
 *
 * @package KSP\View\Navigation\App
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
interface IEntry extends IObject {

    public const ICON_CIRCLE  = "far fa-circle";
    public const ICON_HISTORY = "fas fa-history";
    public const ICON_SHARE   = "fas fa-share";
    public const ICON_SITEMAP = "fas fa-sitemap";
    public const ICON_HOME    = "fas fa-home";
    public const ICON_STAR    = "fas fa-star";

    public function getTitle(): string;

    public function getSelector(): string;

    public function getHref(): ?string;

    public function getIconClass(): string;

}
