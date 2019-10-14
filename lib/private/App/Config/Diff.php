<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or indirectly through a Keestash authorized reseller or distributor (a "Reseller").
 * Please read this EULA agreement carefully before completing the installation process and using the Keestash software. It provides a license to use the Keestash software and contains warranty information and liability disclaimers.
 */

namespace Keestash\App\Config;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use KSP\App\Config\IApp as InstalledApp;
use KSP\App\IApp;
use KSP\App\IApp as LoadedApp;

class Diff {

    public function needsUpgrade(LoadedApp $loadedApp, InstalledApp $installedApp): bool {
        return $this->isLessVersion($loadedApp, $installedApp);
    }

    private function isLessVersion(LoadedApp $loadedApp, InstalledApp $installedApp): bool {
        return $loadedApp->getVersion() < $installedApp->getVersion();
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

    public function removeDisabledApps(HashTable $loadedApps, HashTable $installedApps) {
        foreach ($installedApps->keySet() as $key) {
            /** @var InstalledApp $app */
            $app = $installedApps->get($key);
            if (false === $app->isEnabled()) {
                Keestash::getServer()->getAppLoader()->unloadApp($key);
                // just to be sure :-)
                $loadedApps->remove($key);
            }
        }
    }

}