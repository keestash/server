#!/usr/bin/php
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

use Keestash\Core\Service\Phinx\Migrator;

define("MODE_INSTANCE", "instance");
define("MODE_APP", "app");

$mode = getMode();
if (false === isValidOption($mode)) {
    echo "Please provide a mode" . PHP_EOL;
    echo "Example Call: ./migrate.php -m=instance for instance migrations" . PHP_EOL;
    echo "Example Call: ./migrate.php -m=app for app migrations" . PHP_EOL;
    echo "Check our docs for further information: https://keestash.com" . PHP_EOL;
    exit();
    die();
}

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../lib/Keestash.php";

Keestash::init();
/** @var Migrator $migrator */
$migrator = Keestash::getServer()->query(Migrator::class);

if ($mode === MODE_INSTANCE) {
    $migrator->runCore();
}

if ($mode === MODE_APP) {
    $migrator->runApps();
}

function isValidOption(?string $mode): bool {
    if (null === $mode) return false;
    if (false === in_array($mode, [MODE_INSTANCE, MODE_APP])) return false;
    return true;
}

function getMode(): ?string {
    // m = mode
    // m = instance runs the instance's migrations
    // m = app runs the app's migrations
    $options = 'm:';

    $options = getopt($options);

    return $options['m'] ?? null;

}