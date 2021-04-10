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

use Keestash;
use KSP\App\ILoader;

/**
 * Class Helper
 * @package Keestash\App
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * @deprecated
 */
class Helper {

    private function __construct() {
    }

    /**
     * @param ILoader $loader
     * @return string
     * @deprecated
     */
    public static function getDefaultRoute(ILoader $loader): string {
        $defaultApp = $loader->getDefaultApp();
        $route      = (null === $defaultApp) ? "login" : $defaultApp->getBaseRoute();
        return "$route";
    }

    private function __clone() {

    }

}