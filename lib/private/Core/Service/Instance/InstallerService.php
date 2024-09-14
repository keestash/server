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

namespace Keestash\Core\Service\Instance;

use Keestash;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Core\Service\Instance\IInstallerService;

class InstallerService implements IInstallerService {

    public const PHINX_MIGRATION_EVERYTHING_WENT_FINE = 0;

    public function __construct(private readonly InstanceDB $instanceDB) {
    }

    #[\Override]
    public function updateInstaller(string $key, string $value): bool {
        return $array = $this->instanceDB->updateOption($key, $value);
    }

    #[\Override]
    public function hasIdAndHash(): bool {
        $hash = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH);
        $id   = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_ID);
        return true === is_string($hash) && true === is_int((int) $id);
    }

    #[\Override]
    public function writeIdAndHash(): bool {
        $addedId   = $this->instanceDB->addOption(InstanceDB::OPTION_NAME_INSTANCE_ID, (string) hexdec(uniqid()));
        $addedHash = $this->instanceDB->addOption(InstanceDB::OPTION_NAME_INSTANCE_HASH, bin2hex(random_bytes(16)));
        return true === $addedId && true === $addedHash;
    }

}
