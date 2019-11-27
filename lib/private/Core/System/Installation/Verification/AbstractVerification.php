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

namespace Keestash\Core\System\Installation\Verification;

abstract class AbstractVerification {

    private $messages = [];

    public abstract function hasProperty(): bool;

    protected function addMessage(string $key, string $message): void {
        $this->messages[static::class][$key][] = $message;
    }

    protected function countMessages(string $key, bool $force = false): void {

        $count = 0;
        if (true === array_key_exists($key, $this->messages[static::class] ?? [])) {
            $count = count($this->messages[static::class][$key]);
        }

        if (true === $force || $count > 0) {
            $this->messages[static::class][$key]["size"] = $count;
        }

    }

    public function getMessages(): array {
        return $this->messages;
    }

}