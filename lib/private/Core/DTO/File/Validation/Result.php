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

namespace Keestash\Core\DTO\File\Validation;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\DTO\File\Validation\IResult;

class Result implements IResult {

    private readonly ArrayList $result;

    public function __construct() {
        $this->result = new ArrayList();
    }

    #[\Override]
    public function getResults(): ArrayList {
        return $this->result;
    }

    public function add(string $message): void {
        $this->result->add($message);
    }

    #[\Override]
    public function jsonSerialize(): array {
        return $this->result->toArray();
    }

}