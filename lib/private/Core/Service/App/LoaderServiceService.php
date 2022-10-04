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

namespace Keestash\Core\Service\App;

use doganoo\PHPAlgorithms\Datastructure\Cache\LRUCache;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Exception\DuplicatedSameOrderException;
use KSP\Core\DTO\App\IApp;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\App\ILoaderService;
use Laminas\Config\Config;

/**
 * Class Loader
 * @package Keestash\App
 */
class LoaderServiceService implements ILoaderService {

    private HashTable   $apps;
    private LRUCache    $lruAppCache;
    private Config      $config;
    private IAppService $appService;

    public function __construct(
        Config $config
        , IAppService $appService
    ) {
        $this->apps        = new HashTable();
        $this->lruAppCache = new LRUCache();
        $this->config      = $config;
        $this->appService  = $appService;
    }

    public function getApps(): HashTable {
        $this->loadApps();
        return $this->apps;
    }

    private function loadApps(bool $force = false): void {
        if (0 === $this->apps->size() || true === $force) {
            $appList = $this->config->get(Keestash\ConfigProvider::APP_LIST)
                ->toArray();

            foreach ($appList as $id => $app) {
                $app = $this->appService->toApp((string) $id, (array) $app);
                $this->overrideDefaultApp($app);
                $this->apps->put($app->getId(), $app);
            }
        }
    }

    private function overrideDefaultApp(IApp $app): void {
        $currentAppKey = $this->lruAppCache->last();
        /** @var IApp|null $currentApp */
        $currentApp = $this->lruAppCache->get($currentAppKey);

        if (null === $currentApp) {
            $this->lruAppCache->put($app->getId(), $app);
            return;
        }

        if ($app->getOrder() === $currentApp->getOrder() && $app->getId() !== $currentApp->getId()) {
            throw new DuplicatedSameOrderException("there are two apps with the same order ({$app->getName()} and {$currentApp->getName()})");
        }
        if ($app->getOrder() < $currentApp->getOrder()) {
            $this->lruAppCache->put($app->getId(), $app);
        }
    }

    public function getDefaultApp(): ?IApp {
        $this->loadApps();
        return $this->lruAppCache->get(
            $this->lruAppCache->last()
        );
    }

    public function hasApp(string $name): bool {
        $this->loadApps();
        return $this->apps->containsKey($name);
    }

    public function unloadApp(string $key): bool {
        return true;
    }

}
