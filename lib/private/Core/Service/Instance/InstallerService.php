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

use DateTime;
use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\System\Installation\Instance\LockHandler;
use Keestash\Core\System\Installation\Verification\AbstractVerification;
use Keestash\Core\System\Installation\Verification\ConfigFileReadable;
use Keestash\Core\System\Installation\Verification\DatabaseReachable;
use Keestash\Core\System\Installation\Verification\HasMigrations;
use KSP\Core\Backend\IBackend;
use KSP\Core\Service\Config\IConfigService;
use Laminas\Config\Config;

class InstallerService {

    public const PHINX_MIGRATION_EVERYTHING_WENT_FINE = 0;

    private array          $messages;
    private LockHandler    $lockHandler;
    private Migrator       $migrator;
    private InstanceDB     $instanceDB;
    private IConfigService $configService;
    private Config         $config;
    private IBackend       $backend;

    public function __construct(
        LockHandler $lockHandler
        , Migrator $migrator
        , InstanceDB $instanceDB
        , IConfigService $configService
        , Config $config
        , IBackend $backend
    ) {
        $this->messages      = [];
        $this->lockHandler   = $lockHandler;
        $this->migrator      = $migrator;
        $this->instanceDB    = $instanceDB;
        $this->configService = $configService;
        $this->config        = $config;
        $this->backend       = $backend;
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
            $inserted    = $this->instanceDB->addOption($key, (string) json_encode($value));
            $insertedAll = $insertedAll || $inserted;
        }

        return $insertedAll;
    }

    public function hasIdAndHash(): bool {
        $hash = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH);
        $id   = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_ID);

        return true === is_string($hash) && true === is_int((int) $id);
    }

    public function writeIdAndHash(): bool {
        $addedId   = $this->instanceDB->addOption(InstanceDB::OPTION_NAME_INSTANCE_ID, (string) hexdec(uniqid()));
        $addedHash = $this->instanceDB->addOption(InstanceDB::OPTION_NAME_INSTANCE_HASH, md5(uniqid()));
        return true === $addedId && true === $addedHash;
    }

    public function writeProductionMode(): bool {
        return $this->instanceDB->addOption(InstanceDB::OPTION_NAME_PRODUCTION_MODE, (new DateTime())->format(DateTimeInterface::ATOM));
    }

    private function verifyField(string $name, bool $force = false): array {
        $isInstalled = $this->hasIdAndHash();
        $messages    = [];

        if (true === $isInstalled && false === $force) return $messages;

        /** @var AbstractVerification $verifier */
        $verifier = new $name(
            $this->config
        );
        $verifier->hasProperty();
        $messages     = $verifier->getMessages();
        $messagesSize = count($messages);

        if (0 === $messagesSize) {
            return [];
        }

        $this->updateInstaller($name, (string) json_encode($messages));
        return $messages;
    }

    public function verifyConfigurationFile(bool $force = false): array {
        return $this->verifyField(ConfigFileReadable::class, $force);
    }

    public function isInstalled(): bool {
        $isInstalled = $this->hasIdAndHash();
        if (true === $isInstalled) return true;

        $list = new ArrayList();
        $list->add(new ConfigFileReadable(
            $this->config
        ));
        $list->add(new DatabaseReachable(
            $this->backend
        ));
        $list->add(new HasMigrations(
            $this->config
            , $this->configService
        ));

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

        return false === $hasErrors;
    }

    public function runCoreMigrations(): bool {
        return $this->migrator->runCore();
    }

}
