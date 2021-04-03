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

namespace Keestash\Core\Service\Phinx;

use Keestash;
use Keestash\Core\Service\Instance\InstallerService;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Config\IConfigService;
use Laminas\Config\Config;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class Migrator {

    private string         $phinxRoot;
    private ILogger        $logger;
    private Config         $config;
    private IConfigService $configService;

    public function __construct(
        ILogger $logger
        , Config $config
        , IConfigService $configService
    ) {
        $this->logger        = $logger;
        $this->config        = $config;
        $this->phinxRoot     = (string) $this->config->get(Keestash\ConfigProvider::PHINX_PATH);
        $this->configService = $configService;
    }

    public function runCore() {
        $file   = $this->phinxRoot . "/instance.php";
        $exists = $this->checkFile($file);
        if (false === $exists) {
            $this->logger->debug("file $file does not exist");
            return false;
        }
        return $this->run($file);
    }

    public function runApps() {
        $file   = $this->phinxRoot . "/apps.php";
        $exists = $this->checkFile($file);
        if (false === $exists) {
            return false;
        }
        return $this->run($file);
    }

    private function checkFile(string $path): bool {
        if (false === is_file($path)) {
            $this->logger->debug("The phinx file located at $path is missing. Please add this file and run again.");
            return false;
        }
        return true;
    }

    private function run(string $configPath): bool {

        $debug    = $this->configService->getValue("debug", false);
        $phinxEnv = (true === $debug) ? "development" : "production";

        $phinxApp         = new PhinxApplication();
        $phinxTextWrapper = new TextWrapper($phinxApp);

        $phinxTextWrapper->setOption('configuration', $configPath);
        $phinxTextWrapper->setOption('parser', 'PHP');
        $phinxTextWrapper->setOption('environment', $phinxEnv);

        $log = $phinxTextWrapper->getMigrate();

        $this->logger->debug($log);

        return $phinxTextWrapper->getExitCode() === InstallerService::PHINX_MIGRATION_EVERYTHING_WENT_FINE;
    }

}
