<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KST\Service\Core\Service\Phinx;

use Keestash\ConfigProvider;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Config\IConfigService;
use Laminas\Config\Config;

class Migrator extends \Keestash\Core\Service\Phinx\Migrator {

    private Config $config;

    public function __construct(
        ILogger $logger
        , Config $config
        , IConfigService $configService
    ) {
        parent::__construct($logger, $config, $configService);
        $this->config = $config;
    }

    protected function getFilePath(): string {
        return $this->config->get(ConfigProvider::TEST_PATH) . '/config/phinx/';
    }

}