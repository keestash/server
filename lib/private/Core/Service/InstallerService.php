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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use Keestash;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\System\Installation\Instance\HealthCheck;
use Keestash\Core\System\Installation\Instance\LockHandler;
use Keestash\Core\System\Installation\Verification\AbstractVerification;
use Keestash\Core\System\Installation\Verification\ConfigFileReadable;
use Keestash\Core\System\Installation\Verification\DatabaseReachable;
use Keestash\Core\System\Installation\Verification\DirsWritable;
use Keestash\Core\System\Installation\Verification\HasDataDirs;
use Keestash\Core\System\Installation\Verification\HasMigrations;

class InstallerService {

    public const PHINX_MIGRATION_EVERYTHING_WENT_FINE = 0;

    private $installerFile = null;
    private $healthCheck   = null;
    private $messages      = null;
    private $lockHandler   = null;
    private $migrator      = null;
    private $instanceDB    = null;

    public function __construct(
        LockHandler $lockHandler
        , Migrator $migrator
        , InstanceDB $instanceDB
    ) {
        $this->installerFile = Keestash::getServer()->getInstallerRoot() . "instance.installation";
        $this->healthCheck   = new HealthCheck();
        $this->messages      = [];
        $this->lockHandler   = $lockHandler;
        $this->migrator      = $migrator;
        $this->instanceDB    = $instanceDB;
    }

    public function removeInstaller(): bool {
        $unlocked = $this->lockHandler->unlock();
        if (false === $unlocked) return false;
        return true;
    }

    public function isEmpty(): bool {
        $array = $this->instanceDB->getAll();
        return null === $array || (is_array($array) && 0 === count($array));
    }

    public function updateInstaller(string $key, string $value): bool {
        return $array = $this->instanceDB->updateOption($key, $value);
    }

    public function writeInstaller(array $messages): bool {
        $insertedAll = false;

        foreach ($messages as $key => $value) {
            $inserted    = $this->instanceDB->addOption($key, json_encode($value));
            $insertedAll = $insertedAll || $inserted;
        }

        return $insertedAll;
    }

    public function isInstalled(): bool {
        // if the instance is installed, we have
        // stored the information into a install
        // file. We do not need to check again
        if (true === $this->healthCheck->readInstallation()) return true;


        $list = new ArrayList();
        $list->add(new DirsWritable());
        $list->add(new ConfigFileReadable());
        $list->add(new HasDataDirs());
        $list->add(new DatabaseReachable());
        $list->add(new HasMigrations());

        /** @var AbstractVerification $verification */
        foreach ($list as $verification) {
            $hasProperty = $verification->hasProperty();
            if (false === $hasProperty) {
                $this->messages = array_merge(
                    $this->messages
                    , $verification->getMessages()
                );
            }
        }

        $hasErrors = count($this->messages) > 0;

        if (true === $hasErrors) {
            $this->lockHandler->lock();
            $this->writeInstaller(
                $this->messages
            );
        }

        if (false === $hasErrors) {
            $this->healthCheck->storeInstallation();
        }

        return false === $hasErrors;
    }

    public function runCoreMigrations(): bool {
        $path = Keestash::getServer()->getConfigRoot() . "phinx/instance.php";
        $path = realpath($path);
        return $this->migrator->run($path);

    }

}