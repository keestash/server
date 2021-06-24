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

namespace Keestash\App;

use DateTime;
use doganoo\Backgrounder\BackgroundJob\JobList;
use KSP\App\IApp;

/**
 * Class AppFactory
 * @package Keestash\App
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * @deprecated
 */
class AppFactory {

    public static function toConfigApp(IApp $app): \KSP\App\Config\IApp {
        $configApp = new \Keestash\App\Config\App();
        $configApp->setId($app->getId());
        $configApp->setEnabled(true);
        $configApp->setCreateTs(new DateTime());
        $configApp->setVersion($app->getVersion());
        $configApp->setBackgroundJobs(
            new JobList() // TODO implement me
        );
        return $configApp;
    }

}
