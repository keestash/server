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

namespace KSP\Core\Manager\TemplateManager;

use KSP\Core\Manager\IManager;

interface ITemplateManager extends IManager {

    public function addStylesheet(string $href): void;

    public function addScript(string $path, string $application): void;

    public function addAppScript(string $path, string $route, string $application): void;

    public function addPath(string $path): void;

    public function render(string $name): string;

    public function replace(string $name, array $value): void;

    public function getStylesheets(): array;

    public function getScripts(): array;

    public function getAppScripts(?string $route): array;

    public function getRawTemplate(string $name): string;

}