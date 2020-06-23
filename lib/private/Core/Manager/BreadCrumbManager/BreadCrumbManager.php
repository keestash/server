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

namespace Keestash\Core\Manager\BreadCrumbManager;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\Manager\BreadCrumbManager\IBreadCrumbManager;
use KSP\Core\View\BreadCrumb\IBreadCrumb;

class BreadCrumbManager implements IBreadCrumbManager {

    private $breadCrumbs = null;
    private $visible     = true;

    public function __construct() {
        $this->breadCrumbs = new ArrayList();
    }

    public function add(IBreadCrumb $breadCrumb): void {
        $this->breadCrumbs->add($breadCrumb);
    }

    public function getAll(): ArrayList {
        return $this->breadCrumbs;
    }

    public function isVisible(): bool {
        return $this->visible;
    }

    public function setVisible(bool $visible): void {
        $this->visible = $visible;
    }

}
