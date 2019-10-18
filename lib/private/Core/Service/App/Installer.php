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

namespace Keestash\Core\Service\App;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\App\AppFactory;
use Keestash\Core\Service\Phinx\Migrator;
use KSP\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;

class Installer {

    private $migrator      = null;
    private $appRepository = null;

    public function __construct(
        Migrator $migrator
        , IAppRepository $appRepository
    ) {
        $this->migrator      = $migrator;
        $this->appRepository = $appRepository;
    }

    public function runMigrations(): bool {
        $path = Keestash::getServer()->getConfigRoot() . "phinx/apps.php";
        $path = realpath($path);
        return $this->migrator->run($path);
    }

    public function installAll(HashTable $apps): bool {
        $installed = true;

        foreach ($apps->keySet() as $key) {
            $app          = $apps->get($key);
            $configApp    = AppFactory::toConfigApp($app);
            $appInstalled = $this->install($configApp);
            $installed    = $installed && $appInstalled;
        }
        return $installed;
    }

    public function install(IApp $app): bool {
        return $this->appRepository->replace($app);
    }

}