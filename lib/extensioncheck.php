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

if (defined('__PHPUNIT_MODE__') && __PHPUNIT_MODE__) return;

$loadedExtensions = get_loaded_extensions();

$requiredExtensions = [
    "mysqli"
    , "pdo_mysql"
    , "mysqlnd"
    , "mbstring"
    , "dom"
    , "curl"
    , "sqlite3"
    , "pdo_sqlite"
    , "zip"
    , "intl"
];

$diffed = array_diff(
    $requiredExtensions
    , $loadedExtensions
);

if (count($diffed) !== 0) {
    echo 'There are some extensions missing on your system. Please make sure that all of the following extensions are installed:' . PHP_EOL;
    echo var_export($requiredExtensions) . PHP_EOL;
    echo 'The following are missing:' . PHP_EOL;
    echo var_export($diffed) . PHP_EOL;
}
