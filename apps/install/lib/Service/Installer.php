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

namespace KSA\Install\Service;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\App\AppFactory;
use KSP\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class Installer {

    private $appRepository = null;

    public function __construct(IAppRepository $appRepository) {
        $this->appRepository = $appRepository;
    }

    public const PHINX_MIGRATION_EVERYTHING_WENT_FINE = 0;

    public function runMigrations(): bool {
        $phinxConfig = Keestash::getServer()->getConfigRoot() . "/phinx.php";
        $phinxConfig = realpath($phinxConfig);

        $config   = Keestash::getServer()->getConfig();
        $phinxEnv = (true === $config->get("debug")) ? "development" : "production";

        $phinxApp         = new PhinxApplication();
        $phinxTextWrapper = new TextWrapper($phinxApp);

        $phinxTextWrapper->setOption('configuration', $phinxConfig);
        $phinxTextWrapper->setOption('parser', 'PHP');
        $phinxTextWrapper->setOption('environment', $phinxEnv);

        $log = $phinxTextWrapper->getMigrate();

        // TODO log $log
        return $phinxTextWrapper->getExitCode() === Installer::PHINX_MIGRATION_EVERYTHING_WENT_FINE;

    }

    public function installAll(HashTable $apps): bool {
        $installed = false;

        foreach ($apps->keySet() as $key) {
            $app       = $apps->get($key);
            $configApp = AppFactory::toConfigApp($app);
            $installed |= $this->install($configApp);
        }
        return $installed;
    }

    public function install(IApp $app): bool {
        return $this->appRepository->replace($app);
    }

}