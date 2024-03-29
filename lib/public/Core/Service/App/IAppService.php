<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSP\Core\Service\App;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\App\IApp;

interface IAppService {

    public function toApp(string $id, array $data): IApp;

    public function toConfigApp(IApp $app): \KSP\Core\DTO\App\Config\IApp;

    public function getAppsThatNeedAUpgrade(HashTable $loadedApps, HashTable $installedApps): HashTable;

    public function getNewlyAddedApps(HashTable $loadedApps, HashTable $installedApps): HashTable;

    public function removeDisabledApps(HashTable $loadedApps, HashTable $installedApps): HashTable;

}