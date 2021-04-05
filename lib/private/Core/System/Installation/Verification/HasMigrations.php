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

namespace Keestash\Core\System\Installation\Verification;

use Keestash;
use Keestash\Core\Service\App\InstallerService;
use KSP\Core\Service\Config\IConfigService;
use Laminas\Config\Config;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class HasMigrations extends AbstractVerification {

    private const PHINX_DEVELOPMENT = "development";
    private const PHINX_PRODUCTION  = "production";

    private Config         $config;
    private IConfigService $configService;

    public function __construct(
        Config $config
        , IConfigService $configService
    ) {
        $this->config        = $config;
        $this->configService = $configService;
    }

    public function hasProperty(): bool {
        return true;
//        return $this->migrate();
    }

    private function migrate(): bool {
        $phinxRoot = (string) $this->config->get(Keestash\ConfigProvider::PHINX_PATH);
        $phinxPath = $phinxRoot . "instance.php";

        $phinxEnv = (true === $this->configService->getValue("debug")) ?
            HasMigrations::PHINX_DEVELOPMENT :
            HasMigrations::PHINX_PRODUCTION;

        $phinxApp         = new PhinxApplication();
        $phinxTextWrapper = new TextWrapper($phinxApp);

        $phinxTextWrapper->setOption('configuration', $phinxPath);
        $phinxTextWrapper->setOption('parser', 'PHP');
        $phinxTextWrapper->setOption('environment', $phinxEnv);

        $log = $phinxTextWrapper->getMigrate();

        print_r($log);

        // TODO log $log
        return $phinxTextWrapper->getExitCode() === InstallerService::PHINX_MIGRATION_EVERYTHING_WENT_FINE;
    }

}
