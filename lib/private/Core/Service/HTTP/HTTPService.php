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

namespace Keestash\Core\Service\HTTP;

use Keestash;
use KSP\Core\Service\Core\Environment\IEnvironmentService;

class HTTPService {

    private IEnvironmentService $environmentService;

    public function __construct(IEnvironmentService $environmentService) {
        $this->environmentService = $environmentService;
    }

    public function getBaseURL(bool $withScript = true, bool $forceIndex = false): string {
        if (true === $this->environmentService->isConsole()) return "";
        $scriptName          = "index.php";
        $scriptNameToReplace = $scriptName;
        if (true === $this->environmentService->isApi()) {
            $scriptName          = "api.php";
            $scriptNameToReplace = $scriptName;
        }
        if (true === $forceIndex) {
            $scriptNameToReplace = "index.php";
        }

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $position = strpos($url, $scriptName);
        $position = false === $position ? 0 : $position;

        if ($withScript) {
            return substr($url, 0, $position) . $scriptNameToReplace;
        } else {
            return substr($url, 0, $position) . "";
        }
    }

    public function buildWebRoute(string $base): string {
        $scriptName          = "index.php";
        $scriptNameToReplace = $scriptName;
        $url                 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $position            = strpos($url, $scriptName);
        $position            = false === $position ? 0 : $position;
        return substr($url, 0, $position) . $scriptNameToReplace . "/" . $base;
    }

    public function getBaseAPIURL(): ?string {
        $baseURL = $this->getBaseURL();
        if (null === $baseURL) return null;
        return str_replace("index.php", "api.php", $baseURL);
    }

    public function getAssetURL(): ?string {
        $baseURL = $this->getBaseURL();
        if (null === $baseURL) return null;
        return str_replace("index.php", "asset.php", $baseURL);
    }

    public function getLoginRoute(): string {
        return $this->buildWebRoute("login");
    }

}
