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

namespace KSP\Core\Service\Instance;

interface IInstallerService {

    public function removeInstaller(): bool;

    public function getAll(): ?array;

    public function updateInstaller(string $key, string $value): bool;

    public function removeOption(string $key): bool;

    public function writeInstaller(array $messages): bool;

    public function hasIdAndHash(): bool;

    public function writeIdAndHash(): bool;

    public function writeProductionMode(): bool;

    public function verifyConfigurationFile(bool $force = false): array;

    public function isInstalled(): bool;

    public function runCoreMigrations(): bool;

}