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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use function array_merge;

class TwigManager implements ITemplateManager {

    private $loader = null;
    private $env    = null;

    private $stylesheet = null;
    private $scripts    = null;
    private $appScripts = null;

    private $map = null;

    public function __construct() {
        $this->stylesheet = [];
        $this->scripts    = [];
        $this->appScripts = [];
        $this->map        = new HashTable();
        $this->loader     = new FilesystemLoader();
        $this->env        = new Environment($this->loader);
    }

    public function setUp(?string $baseURL) {
        if (null === $baseURL) return null;
        $this->env->addFunction(new TwigFunction('app_asset', function ($asset) use ($baseURL) {
            return $baseURL . $asset;
        }));
    }

    public function addStylesheet(string $href): void {
        $this->stylesheet[] = $href;
    }

    public function getStylesheets(): array {
        return $this->stylesheet;
    }

    public function addScript(string $path, string $application = "application/javascript"): void {
        $this->scripts[$path] = $application;
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

    public function addPath(string $path): void {
        $this->loader->addPath($path);
    }

    public function replace(string $name, array $value): void {
        if ($this->map->containsKey($name)) {
            $arr = $this->map->get($name);
            $arr = array_merge($arr, $value);
            $this->map->put($name, $arr);
        } else {
            $this->map->put($name, $value);
        }
    }

    public function getRawTemplate(string $name): string {
        return $this->env->getLoader()->getSourceContext($name)->getCode();
    }

    public function render(string $name): string {
        $variables = [];
        if ($this->map->containsKey($name)) {
            $variables = $this->map->get($name);
        }
        $rendered = $this->env->render($name, $variables);
        return $rendered;
    }

    public function getAppScripts(?string $route): array {
        if (null === $route) return $this->appScripts;
        $route = str_replace("/", "", $route);
        $route = str_replace("_", "", $route);
        $route = strtolower($route);
        return $this->appScripts[$route] ?? [];
    }

}
