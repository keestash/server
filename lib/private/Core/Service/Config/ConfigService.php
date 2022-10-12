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

namespace Keestash\Core\Service\Config;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\Service\Config\IConfigService;

class ConfigService implements IConfigService {

    private HashTable $config;

    /**
     * ConfigService constructor.
     *
     * @param HashTable $config
     */
    public function __construct(HashTable $config) {
        $this->config = $config;
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getValue(string $key, $default = null) {

        if (true === $this->config->containsKey($key)) {
            return $this->config->get($key);
        }
        return $default;
    }

    /**
     * @return array
     */
    public function getAll(): array {
        return $this->config->toArray();
    }

}
