#!/usr/bin/env php
<?php
declare(strict_types=1);

use Keestash\Command\KeestashCommand;
use Symfony\Component\Console\Application;

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

(function () {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../lib/versioncheck.php';
    require_once __DIR__ . '/../lib/filecheck.php';
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../lib/Keestash.php';
    Keestash::init();
    $consoleManager = Keestash::getServer()->getConsoleManager();
    $commands       = $consoleManager->getSet();
    $cliVersion     = "1.0.0";

    $application = new Application(
        Keestash::getServer()->getLegacy()->getApplication()->get("name") . " CLI Tools"
        , $cliVersion
    );

    /** @var KeestashCommand $command */
    foreach ($commands->toArray() as $command) {
        $application->add($command);
    }

    $application->run();
})();
