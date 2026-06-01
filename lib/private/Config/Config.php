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

namespace Keestash\Config;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Lightweight read-only configuration container.
 * Drop-in replacement for the abandoned laminas/laminas-config.
 *
 * @implements ArrayAccess<string|int, mixed>
 * @implements IteratorAggregate<string|int, mixed>
 */
final class Config implements ArrayAccess, Countable, IteratorAggregate {

    private array $data;

    public function __construct(array $data) {
        $this->data = array_map(
            static fn(mixed $value): mixed => is_array($value) ? new self($value) : $value,
            $data
        );
    }

    public function get(string|int $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $default;
    }

    public function toArray(): array {
        return array_map(
            static fn(mixed $value): mixed => $value instanceof self ? $value->toArray() : $value,
            $this->data
        );
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        // read-only
    }

    public function offsetUnset(mixed $offset): void {
        // read-only
    }

    public function count(): int {
        return count($this->data);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->data);
    }

}
