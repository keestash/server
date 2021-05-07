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

namespace Keestash\Core\Service\Stylesheet;

use Keestash\ConfigProvider;
use Keestash\Exception\StylesheetsNotCompiledException;
use Laminas\Config\Config;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;

/**
 * Class Compiler
 *
 * @package Keestash\Core\Service\Stylesheet
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Compiler {

    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @throws StylesheetsNotCompiledException
     */
    public function compile(string $source, string $destination): void {
        $compiler = new ScssCompiler();
        $compiler->addImportPath(realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/vendor/twitter/bootstrap/scss'));
        $compiler->addImportPath(realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/lib/scss/'));
        $compiler->addImportPath(realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/node_modules/'));

//        $compiler->addImportPath($source);
        $css = $compiler->compile(
            file_get_contents($source)
        );

        if (true === is_file($destination)) {
            unlink($destination);
        }

        $created = file_put_contents($destination, $css);

        if (false === $created || false === is_file($destination)) {
            throw new StylesheetsNotCompiledException();
        }
    }

}
