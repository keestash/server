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

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../lib/Keestash.php";

Keestash::init();

$appRoot  = Keestash::getServer()->getServerRoot();
$baseDist = "$appRoot/lib/js/dist/base.bundle.js";

$appRoot = Keestash::getServer()->getAppRoot();

$files   = glob($appRoot . "/*/js/dist/*.js");
$files[] = $baseDist;

foreach ($files as $file) {
    if (true === is_file($file)) {
        unlink($file);
    }
}