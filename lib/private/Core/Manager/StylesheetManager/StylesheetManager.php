<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Manager\StylesheetManager;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use KSP\App\IApp;
use KSP\Core\Manager\StylesheetManager\IStylesheetManager;
use KSP\Core\Service\HTTP\Route\IRouteService;

/**
 * Class StylesheetManager
 *
 * @package Keestash\Core\Manager\StylesheetManager
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class StylesheetManager implements IStylesheetManager {

    private HashTable     $apps;
    private ?array        $cache = null;
    private IRouteService $routeService;

    private array $route = [];

    public function __construct(IRouteService $routeService) {
        $this->apps         = new HashTable();
        $this->routeService = $routeService;
    }

    public function register(IApp $app): void {
        $this->apps->put($app->getName(), $app);
        $this->cache = null;
    }

    public function getPathForApp(string $id): ?string {
        if (null === $this->cache) {
            $this->cache = $this->getPaths();
        }
        return $this->cache[$id] ?? null;
    }

    public function getPaths(): array {

        if (null !== $this->cache) {
            return $this->cache;
        }

        $apps = $this->getApps();

        $paths = [];
        foreach ($apps->keySet() as $appId) {

            /** @var IApp $app */
            $app         = $apps->get($appId);
            $key         = $this->routeService->routeToAppId($appId);
            $paths[$key] = Keestash::getBaseURL(false) . "/apps/{$app->getId()}/scss/dist/style.css";
        }

        $this->cache = $paths;
        return $paths;
    }

    public function getApps(): HashTable {
        return $this->apps;
    }

    public function registerForRoute(string $route, string $name): void {
        $this->route[$route] = $name;
    }

    public function get(string $route): ?string {
        return $this->route[$route] ?? null;
    }

}
