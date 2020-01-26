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

namespace Keestash\Core\Manager\RouterManager;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\Service\DateTimeService;
use KSP\Core\Backend\IBackend;
use KSP\Core\Manager\RouterManager\IRouter;
use KSP\Core\Manager\RouterManager\IRouterManager;

class RouterManager implements IRouterManager {

    private $routers = null;

    public function __construct(?IBackend $backend, ?DateTimeService $dateTimeService = null) {
        $this->routers = new HashTable();
    }

    public function add(string $name, IRouter $router): bool {
        return $this->routers->put($name, $router);
    }

    public function get(string $name): ?IRouter {
        if (false === $this->routers->containsKey($name)) return null;
        /** @var IRouter $router */
        $router = $this->routers->get($name);

        if ($router instanceof IRouter) return $router;
        return null;
    }

}