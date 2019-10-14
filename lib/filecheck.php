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

$config        = __DIR__ . "/../config/config.php";
$configSample  = __DIR__ . "/../config/config.sample.php";
$appsPhinx     = __DIR__ . "/../config/phinx/apps.php";
$instancePhinx = __DIR__ . "/../config/phinx/instance.php";

$files = [
    $config
    , $configSample
    , $appsPhinx
    , $instancePhinx
];

foreach ($files as $index => $file) {

    if (true === is_file($file)) {
        unset($files[$index]);
    }
}

$missingFileSize = count($files);

if ($missingFileSize > 0) {
    echo 'The following files are missing on your instance. ' . PHP_EOL;
    echo 'Please create them and run your request again: ' . PHP_EOL;
    echo PHP_EOL;
    echo implode($files, "<br>") . PHP_EOL;
    echo PHP_EOL;
    echo "Check our docs for further information: https://keestash.com" . PHP_EOL;
    exit();
}