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

    public function __construct(
        LockHandler $lockHandler
        , Migrator $migrator
    ) {
        $this->installerFile = Keestash::getServer()->getInstallerRoot() . "instance.installation";
        $this->healthCheck   = new HealthCheck();
        $this->messages      = [];
        $this->lockHandler   = $lockHandler;
        $this->migrator      = $migrator;
    }

    public function removeInstaller(): bool {
        $deleted = unlink($this->installerFile);
        if (false === $deleted) return false;
        $unlocked = $this->lockHandler->unlock();
        if (false === $unlocked) return false;
        return true;
    }

    public function getInstaller(): ?array {
        if (false === is_file($this->installerFile)) return null;

        $content = file_get_contents(
            $this->installerFile
        );
        if (false === $content) return null;

        $array = json_decode(
            $content
            , true
        );

        if (null === $array) return null;

        return $array;
    }

    public function isEmpty(): bool {
        $array = $this->getInstaller();
        return null === $array || (is_array($array) && 0 === count($array));
    }

    public function updateInstaller(string $key, $value = null): bool {
        $array = $this->getInstaller();
        if (null === $array) return false;
        if (false === isset($array[$key])) return false;

        if (null === $value) {
            unset($array[$key]);
        } else {
            $array[$key] = $value;
        }

        return $this->writeInstaller($array);
    }

    public function writeInstaller(array $messages): bool {
        $put = file_put_contents(
            $this->installerFile
            , json_encode(
                $messages
                , JSON_PRETTY_PRINT
            )
        );
        return false !== $put;
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