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

namespace Keestash\Core\Manager\ConsoleManager;

use doganoo\PHPAlgorithms\Datastructure\Sets\HashSet;
use Keestash\Command\KeestashCommand;
use KSP\Core\Manager\ConsoleManager\ICommandSet;

/**
 * Class CommandSet
 * @package Keestash\Core\Manager\ConsoleManager
 */
class CommandSet implements ICommandSet {

    /** @var array $commands */
    private $commands = [];

    public function add(KeestashCommand $element): bool {
        $this->commands[$element->getName()] = $element;
        return true;
    }

    public function getCommands(): array {
        return $this->commands;
    }

}
