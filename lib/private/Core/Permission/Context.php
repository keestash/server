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

namespace Keestash\Core\Permission;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Common\IUser;
use KSP\Core\Permission\IContext;

/**
 * Class Context
 * @package Keestash\Core\Permission
 */
class Context implements IContext {

    /** @var HashTable|null $attributes */
    private $attributes = null;

    /**
     * Context constructor.
     */
    public function __construct() {
        $this->attributes = new HashTable();
    }

    /**
     * the user for whom the check should made
     *
     * @param IUser $user
     */
    public function addUser(IUser $user): void {
        $this->attributes->put(IContext::USER, $user);
    }

    /**
     * returns an attribute
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name) {
        return $this->attributes->get($name);
    }

}