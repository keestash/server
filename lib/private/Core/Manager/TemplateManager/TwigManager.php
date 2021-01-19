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

namespace Keestash\Core\Manager\TemplateManager;

use Twig\TwigFunction;

class TwigManager extends TemplateManager {

    private array $stylesheet;
    private array $scripts;
    private array $appScripts;

    public function __construct() {
        $this->stylesheet = [];
        $this->scripts    = [];
        $this->appScripts = [];

        parent::__construct();
    }

    public function setUp(?string $baseURL): void {
        if (null === $baseURL) return;
        $this->getEnvironment()
            ->addFunction(new TwigFunction('app_asset', function ($asset) use ($baseURL) {
                return $baseURL . $asset;
            }));
    }

    public function addAppScript(
        string $path
        , string $route
        , string $application = "application/javascript"
    ): void {
        $route                           = str_replace("/", "", $route);
        $route                           = str_replace("_", "", $route);
        $route                           = strtolower($route);
        $this->appScripts[$route][$path] = $application;
    }

    public function getScripts(): array {
        return $this->scripts;
    }

    public function getAppScripts(?string $route): array {
        if (null === $route) return $this->appScripts;
        return $this->appScripts[$route] ?? [];
    }

}
