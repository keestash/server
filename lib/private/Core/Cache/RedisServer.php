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

namespace Keestash\Core\Cache;

use Exception;
use Keestash\Core\Service\Config\ConfigService;
use KSP\Core\Cache\ICacheServer;
use KSP\Core\ILogger\ILogger;
use Redis;

/**
 * Class RedisServer
 * @package Keestash\Core\Cache
 */
class RedisServer implements ICacheServer {

    private bool          $connected = false;
    private Redis         $instance;
    private ILogger       $logger;
    private ConfigService $config;

    public function __construct(
        ILogger $logger
        , ConfigService $config
    ) {
        $this->instance = new Redis();
        $this->logger   = $logger;
        $this->config   = $config;
        $this->connect();
    }

    public function connect(): void {
        if (true === $this->connected) return;
        try {
            $this->instance->connect(
                $this->config->getValue('redis_server', '')
                , $this->config->getValue('redis_port', 0)
            );
            $this->connected = true;
        } catch (Exception $e) {
            $this->logger->warning('could not connect to redis server: ' . $e->getMessage());
        }
    }

    public function set(string $key, $value): bool {
        if (false === $this->connected) return false;
        return $this->instance->set($key, $value);
    }

    public function get(string $key) {
        if (false === $this->connected) return null;
        return $this->instance->get($key);
    }

    public function exists(string $key): bool {
        if (false === $this->connected || $this->config->getValue('debug', false)) return false;
        return (bool) $this->instance->exists($key);
    }

}