#!/usr/bin/env php
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

use Keestash\Exception\StylesheetsNotCompiledException;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;

$action = $argv[1] ?? 'add';

(function () use ($action) {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../vendor/autoload.php';

    createDirIfNotExists(__DIR__ . '/../lib/scss/dist/');
    compile(
            __DIR__ . '/../lib/scss/',
            __DIR__ . '/../lib/scss/dist/style.css',
    );

    foreach (glob(__DIR__ . '/../apps/*/scss/') as $directory) {
        createDirIfNotExists($directory . '/dist/');

        if ('add' === $action) {
            compile($directory, $directory . '/dist/style.css');
        } else {
            remove($directory . '/dist');
        }
    }

})();

function createDirIfNotExists(string $dir): bool {

    if (true === is_dir($dir)) return true;

    $created = mkdir($dir, 0777, true);

    if (false === $created) {
        throw new Exception('could not create ' . $dir);
    }
    return true;
}

function remove(string $dir): bool {
    foreach (glob($dir . '/*.css') as $file) {
        unlink($file);
    }
    return true;
}

function compile(string $source, string $destination): void {
    $compiler = new ScssCompiler();
    $compiler->addImportPath($source);
    $css = $compiler->compile('@import "style.scss";');

    if (true === is_file($destination)) {
        unlink($destination);
    }

    $created = file_put_contents($destination, $css);

    if (false === $created || false === is_file($destination)) {
        throw new StylesheetsNotCompiledException();
    }
}