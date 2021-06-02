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
        /** @phpstan-ignore-next-line */
        $compiler->addImportPath(realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/vendor/twitter/bootstrap/scss'));
        /** @phpstan-ignore-next-line */
        $compiler->addImportPath(realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/lib/scss/'));
        /** @phpstan-ignore-next-line */
        $compiler->addImportPath(realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/node_modules/'));

        $css = $compiler->compile(
            (string) file_get_contents($source)
        );

        if (true === is_file($destination)) {
            unlink($destination);
        }

        $created = file_put_contents($destination, $css);
        $isFile  = is_file($destination);

        if (false === $created || false === $isFile) {
            throw new StylesheetsNotCompiledException(
                'created: ' . (true === $created ? 'true' : 'false')
                . ' is file: ' . (true === $isFile ? 'true' : 'false')
                . ' at destination: ' . $destination
            );
        }
    }

}
