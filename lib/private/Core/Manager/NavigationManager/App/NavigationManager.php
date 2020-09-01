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

namespace Keestash\Core\Manager\NavigationManager\App;

use Keestash\View\Navigation\App\NavigationList;
use KSP\Core\Manager\NavigationManager\INavigationManager;

/**
 * Class NavigationManager
 *
 * @package Keestash\Core\Manager\NavigationManager\App
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class NavigationManager implements INavigationManager {

    private NavigationList $navigationList;

    public function __construct() {
        $this->navigationList = new NavigationList();
    }

    public function setList(NavigationList $navigationList): void {
        $this->navigationList = $navigationList;
    }

    public function getList(): NavigationList {
        return $this->navigationList;
    }

}
