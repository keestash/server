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

namespace Keestash\Core\System\Installation;

use Keestash;
use Keestash\Core\Repository\Instance\InstanceDB;

abstract class LockHandler {

    private $instanceDb = null;

    public function __construct(InstanceDB $instanceDB) {
        $this->instanceDb = $instanceDB;
    }

    public function isLocked(): bool {
        return (bool) $this->instanceDb->getOption($this->getDomain());
    }

    public function lock(): bool {
        if (true === $this->isLocked()) return true;

        return $this->instanceDb->addOption(
            $this->getDomain()
            , (string) getmypid()
        );

    }

    public function unlock(): bool {
        if (false === $this->isLocked()) return true;
        return $this->instanceDb->removeOption($this->getDomain());
    }

    public abstract function getDomain(): string;

}