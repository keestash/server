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

namespace KSP\Core\View\Navigation;

use DateTime;
use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;

interface IEntry extends IComparable {

    public function getId(): string;

    public function getName(): string;

    public function getOrder(): int;

    public function getFAClass(): ?string;

    public function getStartDate(): ?DateTime;

    public function getEndDate(): ?DateTime;

    public function isFavorite(): bool;

    public function isVisible(): bool;

}