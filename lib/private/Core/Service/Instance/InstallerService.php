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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\System\Installation\Instance\LockHandler;
use Keestash\Core\System\Installation\Verification\AbstractVerification;
use Keestash\Core\System\Installation\Verification\ConfigFileReadable;
use Keestash\Core\System\Installation\Verification\DatabaseReachable;
use Keestash\Core\System\Installation\Verification\DirsWritable;
use Keestash\Core\System\Installation\Verification\HasDataDirs;
use Keestash\Core\System\Installation\Verification\HasMigrations;

class InstallerService {

    public const PHINX_MIGRATION_EVERYTHING_WENT_FINE = 0;

    private $messages      = null;
    private $lockHandler   = null;
    private $migrator      = null;
    private $instanceDB    = null;
    private $configService = null;

    public function __construct(
        LockHandler $lockHandler
        , Migrator $migrator
        , InstanceDB $instanceDB
        , ConfigService $configService
    ) {
        $this->messages      = [];
        $this->lockHandler   = $lockHandler;
        $this->migrator      = $migrator;
        $this->instanceDB    = $instanceDB;
        $this->configService = $configService;
    }

    public function removeInstaller(): bool {
        $unlocked = $this->lockHandler->unlock();
        if (false === $unlocked) return false;
        return true;
    }


    public function getAll(): ?array {
        return $this->instanceDB->getAll();
    }

    public function updateInstaller(string $key, string $value): bool {
        return $array = $this->instanceDB->updateOption($key, $value);
    }

    public function removeOption(string $key): bool {
        return $this->instanceDB->removeOption($key);
    }

    public function writeInstaller(array $messages): bool {
        $insertedAll = false;

        foreach ($messages as $key => $value) {
            $inserted    = $this->instanceDB->addOption($key, json_encode($value));
            $insertedAll = $insertedAll || $inserted;
        }

        return $insertedAll;
    }

    public function hasIdAndHash(): bool {
        $hash = $this->instanceDB->getOption(InstanceDB::FIELD_NAME_INSTANCE_HASH);
        $id   = $this->instanceDB->getOption(InstanceDB::FIELD_NAME_INSTANCE_ID);

        return true === is_string($hash) && true === is_int((int) $id);
    }

    public function writeIdAndHash(): bool {
        $addedId   = $this->instanceDB->addOption(InstanceDB::FIELD_NAME_INSTANCE_ID, (string) hexdec(uniqid()));
        $addedHash = $this->instanceDB->addOption(InstanceDB::FIELD_NAME_INSTANCE_HASH, md5(uniqid()));

        return true === $addedId && true === $addedHash;
    }

    private function verifyField(string $name, bool $force = false): array {
        $isInstalled = $this->hasIdAndHash();
        $messages    = [];

        if (true === $isInstalled && false === $force) return $messages;

        /** @var AbstractVerification $verifier */
        $verifier = new $name();
        $verifier->hasProperty();
        $messages     = $verifier->getMessages();
        $messagesSize = count($messages);

        if (0 === $messagesSize) {
            return [];
        }

        $this->updateInstaller($name, json_encode($messages));
        return $messages;
    }

    public function verifyConfigurationFile(bool $force = false): array {
        return $this->verifyField(ConfigFileReadable::class, $force);
    }

    public function verifyWritableDirs(bool $force = false): array {
        return $this->verifyField(DirsWritable::class, $force);
    }

    public function verifyHasDataDirs(bool $force = false): array {
        return $this->verifyField(HasDataDirs::class, $force);
    }

    public function isInstalled(): bool {
        $isInstalled = $this->hasIdAndHash();
        if (true === $isInstalled) return true;

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

        FileLogger::debug(json_encode($this->messages));

        return false === $hasErrors;
    }

    public function runCoreMigrations(): bool {
        return $this->migrator->runCore();
    }

}
