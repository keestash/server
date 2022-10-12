<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Builder\Data;

use Keestash\Core\Service\Core\Data\DataService;
use KSP\Core\Builder\Data\IDataServiceBuilder;
use KSP\Core\Service\Core\Data\IDataService;
use Laminas\Config\Config;

class DataServiceBuilder implements IDataServiceBuilder {

    private string  $appId;
    private ?string $context = null;
    private Config  $config;

    public function withAppId(string $appId): IDataServiceBuilder {
        $instance        = clone $this;
        $instance->appId = $appId;
        return $instance;
    }

    public function withContext(?string $context): IDataServiceBuilder {
        $instance          = clone $this;
        $instance->context = $context;
        return $instance;
    }

    public function withConfig(Config $config): IDataServiceBuilder {
        $instance         = clone $this;
        $instance->config = $config;
        return $instance;

    }

    public function build(): IDataService {
        return new DataService(
            $this->appId
            , $this->config
            , $this->context
        );
    }

}
