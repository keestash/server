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

use doganoo\PHPAlgorithms\Datastructure\Set\HashSet;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use RecursiveDirectoryIterator;
use SplFileInfo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function array_merge;

class TemplateManager implements ITemplateManager {

    private $loader = null;
    private $env    = null;
    private $map    = null;
    private $names  = null;

    public function __construct() {
        $this->names  = new HashSet();
        $this->map    = new HashTable();
        $this->loader = new FilesystemLoader();
        $this->env    = new Environment($this->loader);
    }

    public function addAll(array $paths): void {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    public function addPath(string $path): void {
        $this->loader->addPath($path);

        $iterator = new RecursiveDirectoryIterator($path);
        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            if (
                true === $info->isFile() &&
                IExtension::TWIG === $info->getExtension()
            ) {
                $this->names->add($info->getRealPath());
            }
        }
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

    public function getAllRaw(): HashTable {
        $table = new HashTable();
        foreach ($this->names->toArray() as $name) {
            $baseName = basename($name);

            $template = $this->getRawTemplate(
                $baseName
            );
            $table->put($name, $template);
        }
        return $table;
    }

    public function getRawTemplate(string $name): string {
        return $this->env->getLoader()->getSourceContext($name)->getCode();
    }

    public function render(string $name): string {
        $variables = [];
        if ($this->map->containsKey($name)) {
            $variables = $this->map->get($name);
        }
        return $this->env->render($name, $variables);
    }

    public function getPaths(): HashSet {
        return $this->names;
    }

    protected function getEnvironment(): Environment {
        return $this->env;
    }

}
