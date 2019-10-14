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

namespace KST\Api;

use Keestash\Api\AbstractApi;
use Keestash\Server;
use KSP\L10N\IL10N;
use ReflectionClass;

class SimpleApiRunner {

    public function run(string $name, Server $server, $parameters): AbstractApi {
        $instance = new ReflectionClass($name);
        /** @var AbstractApi $testApi */
        $testApi = $instance->newInstanceArgs(
            [$server->query(IL10N::class)]
        );

        $onCreate = $instance->getMethod("onCreate");
        $onCreate->invokeArgs($testApi, $parameters);

        $testApi->create();
        $testApi->afterCreate();
        return $testApi;
    }

}