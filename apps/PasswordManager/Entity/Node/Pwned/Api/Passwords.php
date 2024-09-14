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

namespace KSA\PasswordManager\Entity\Node\Pwned\Api;

use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use doganoo\PHPAlgorithms\Common\Util\Comparator;

class Passwords implements IComparable {

    public function __construct(private readonly string   $prefix, private readonly string $suffix, private readonly int    $count)
    {
    }

    /**
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getSuffix(): string {
        return $this->suffix;
    }

    public function getFullHash(): string {
        return $this->getPrefix() . $this->getSuffix();
    }

    /**
     * @return int
     */
    public function getCount(): int {
        return $this->count;
    }

    #[\Override]
    public function compareTo($object): int {
        if ($object instanceof Passwords) {
            if (Comparator::equals($this->getFullHash(), $object->getFullHash())) return IComparable::EQUAL;
            if (Comparator::lessThan($this->getFullHash(), $object->getFullHash())) return IComparable::IS_LESS;
            if (Comparator::greaterThan($this->getFullHash(), $object->getFullHash())) return IComparable::IS_GREATER;
        }
        return IComparable::IS_LESS;
    }

}