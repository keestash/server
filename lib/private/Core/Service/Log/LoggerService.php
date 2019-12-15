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

namespace Keestash\Core\Service\Log;

use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Log\Logger;

/**
 * Class LoggerService
 * @package Keestash\Core\Service\Log
 *
 * TODO enable multiple levels
 */
class LoggerService {

    public function file($value): void {
        $value = $this->preProcess($value);
        if (null === $value) return;
        FileLogger::debug($value);
    }

    private function preProcess($value): ?string {
        if (false === is_string($value)) {
            $value = json_encode($value);
        }

        if (false === is_string($value)) {
            FileLogger::debug("we can not log since value is not a string");
            return null;
        }
        return $value;
    }

    public function console($value): void {
        $value = $this->preProcess($value);
        if (null === $value) return;
        Logger::debug($value);
    }

    public function all($value): void {
        $this->file($value);
        $this->console($value);
    }

}