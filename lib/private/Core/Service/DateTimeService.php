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

namespace Keestash\Core\Service;

use DateTime;
use doganoo\PHPUtil\Log\FileLogger;
use Exception;
use function strtotime;

class DateTimeService {

    /**
     * @param string $dateTime
     * @return DateTime|null
     * @throws Exception
     */
    public function fromString(string $dateTime): ?DateTime {
        $time = strtotime($dateTime);

        if (false === $time) return null;

        try {
            $dt = new DateTime();
            $dt = $dt->setTimestamp($time);
        } catch (Exception $exception) {
            FileLogger::error("could not parse $dateTime to \DateTime object");
            return null;
        }

        return $dt;
    }

}