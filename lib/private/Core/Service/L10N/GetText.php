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

namespace Keestash\Core\Service\L10N;

use DateTime;
use KSP\Core\Service\L10N\IL10N;

class GetText implements IL10N {

    public function __construct() {

    }

    public function translate(string $text): string {
        return $text;
    }

    public function localize(string $text): string {
        return $text;
    }

    public function getLanguageCode(): string {
        return "de-DE";
    }

    public function getLocaleCode(): string {
        return "de-DE";
    }

    public function localizeDateTime(DateTime $dateTime): string {
        return $dateTime->format("d.m.Y");
    }

}