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

namespace Keestash\Core\Service\App;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\App\App;
use Keestash\Core\DTO\BackgroundJob\JobList;
use KSP\Core\DTO\App\Config\IApp as InstalledApp;
use KSP\Core\DTO\App\IApp;
use KSP\Core\Service\App\IAppService;

class AppService implements IAppService {

    public function toApp(string $id, array $data): IApp {
        $app = new App();
        $app->setId($id);
        $app->setName((string) $data[ConfigProvider::APP_NAME]);
        $app->setOrder((int) $data[ConfigProvider::APP_ORDER]);
        $app->setVersion((int) $data[ConfigProvider::APP_VERSION]);
        $app->setBaseRoute((string) $data[ConfigProvider::APP_BASE_ROUTE]);
        return $app;
    }

    public function toConfigApp(IApp $app): InstalledApp {
        $configApp = new \Keestash\Core\DTO\App\Config\App();
        $configApp->setId($app->getId());
        $configApp->setEnabled(true);
        $configApp->setCreateTs(new DateTimeImmutable());
        $configApp->setVersion($app->getVersion());
        $configApp->setBackgroundJobs(
            new JobList()
        );
        return $configApp;
    }

    public function getAppsThatNeedAUpgrade(HashTable $loadedApps, HashTable $installedApps): HashTable {
        $result = new HashTable();
        foreach ($loadedApps->keySet() as $key) {
            // if the loaded app is not in the installed apps
            // hash map, this means that the app is added newly to
            // Keestash.
            // We need to keep track and return this app
            if (false === $installedApps->containsKey($key)) {
                continue;
            }

            /** @var IApp $laodedApp */
            $laodedApp = $loadedApps->get($key);

            /** @var InstalledApp $installedApp */
            $installedApp = $installedApps->get($key);

            if ($this->needsUpgrade($laodedApp, $installedApp)) {
                $result->put($key, $loadedApps->get($key));
            }

        }

        return $result;
    }

    public function getNewlyAddedApps(HashTable $loadedApps, HashTable $installedApps): HashTable {
        $result = new HashTable();
        foreach ($loadedApps->keySet() as $key) {
            // if the loaded app is not in the installed apps
            // hash map, this means that the app is added newly to
            // Keestash.
            // We need to keep track and return this app
            if (false === $installedApps->containsKey($key)) {
                $result->put($key, $loadedApps->get($key));
            }
        }

        return $result;
    }

    public function removeDisabledApps(HashTable $loadedApps, HashTable $installedApps): HashTable {
        foreach ($installedApps->keySet() as $key) {
            /** @var InstalledApp $app */
            $app = $installedApps->get($key);
            if (false === $app->isEnabled()) {
                // TODO $this->loaderService->unloadApp($key);
                // just to be sure :-)
                $loadedApps->remove($key);
            }
        }
        return $loadedApps;
    }

    private function needsUpgrade(IApp $loadedApp, InstalledApp $installedApp): bool {
        return $this->isLessVersion($loadedApp, $installedApp);
    }

    private function isLessVersion(IApp $loadedApp, InstalledApp $installedApp): bool {
        return $loadedApp->getVersion() < $installedApp->getVersion();
    }

}