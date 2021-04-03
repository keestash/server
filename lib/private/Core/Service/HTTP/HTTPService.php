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
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;

class HTTPService {

    private HTTPRouter $router;

    public function __construct(HTTPRouter $router) {
        $this->router = $router;
    }

    public function routeToInstallInstance(): void {
        $this->router
            ->routeTo("install_instance");
        exit();
        die();
    }

    public function buildWebRoute(string $base): string {
        $scriptName          = "index.php";
        $scriptNameToReplace = $scriptName;
        $url                 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $position            = strpos($url, $scriptName);
        $position            = false === $position ? 0 : $position;
        return substr($url, 0, $position) . $scriptNameToReplace . "/" . $base;
    }

    public function getLoginRoute(): string {
        return $this->buildWebRoute("login");
    }

}
