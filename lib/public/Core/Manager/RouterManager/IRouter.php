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

namespace KSP\Core\Manager\RouterManager;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\IJsonToken;
use Symfony\Component\Routing\Route;

interface IRouter {

    public const DELETE = "DELETE";
    public const GET    = "GET";
    public const POST   = "POST";
    public const PUT    = "PUT";

    public function addRoute(string $name, array $defaults, array $verbs = ["GET"]): bool;

    public function route(?IJsonToken $token): void;

    public function getRoutes(): ?HashTable;

    public function hasRoute(string $name): bool;

    public function getRoute(string $name): ?Route;

    public function isPublicRoute(): bool;

    public function registerPublicRoute(string $name): bool;

    public function getParameter(string $name): ?string;

}
