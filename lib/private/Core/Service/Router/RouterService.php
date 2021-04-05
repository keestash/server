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

namespace Keestash\Core\Service\Router;

use KSP\Core\Service\Router\IRouterService;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterService implements IRouterService {

    private RouterInterface $router;

    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    public function getMatchedPath(ServerRequestInterface $request): string {
        $matchedRoute = $this->router->match($request)->getMatchedRoute();

        if ($matchedRoute instanceof Route) {
            return $this->router->match($request)->getMatchedRoute()->getPath();
        }
        return '';
    }

}