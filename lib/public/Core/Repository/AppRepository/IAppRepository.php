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

namespace KSP\Core\Repository\AppRepository;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\App\Config\IApp;
use KSP\Core\Repository\IRepository;

interface IAppRepository extends IRepository {

    public function getAllApps(): HashTable;

    public function getApp(string $id): IApp;

    public function replace(IApp $app): bool;

}