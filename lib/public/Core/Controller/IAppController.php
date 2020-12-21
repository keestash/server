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

namespace KSP\Core\Controller;

use KSP\Core\Permission\IPermission;

interface IAppController {

    public const CONTROLLER_TYPE_NORMAL      = 1;
    public const CONTROLLER_TYPE_FULL_SCREEN = 2;
    public const CONTROLLER_TYPE_STATIC      = 3;
    public const CONTROLLER_TYPE_CONTEXTLESS = 4;

    public function onCreate(...$params): void;

    public function create(): void;

    public function afterCreate(): void;

    public function getControllerType(): int;

    public function getPermission(): IPermission;

}