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

use Keestash\Middleware\ApplicationStartedMiddleware;
use Keestash\Middleware\AppsInstalledMiddleware;
use Keestash\Middleware\DispatchMiddleware;
use Keestash\Middleware\Web\ExceptionHandlerMiddleware;
use Keestash\Middleware\InstanceInstalledMiddleware;
use Keestash\Middleware\Web\LoggedInMiddleware;
use Keestash\Middleware\Web\SessionHandlerMiddleware;
use Mezzio\Application;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

return function (Application $app) {
    $app->pipe(ApplicationStartedMiddleware::class);
    $app->pipe(ExceptionHandlerMiddleware::class);
    $app->pipe(SessionHandlerMiddleware::class);
    $app->pipe(BodyParamsMiddleware::class);
    $app->pipe(InstanceInstalledMiddleware::class);
    $app->pipe(AppsInstalledMiddleware::class);
    $app->pipe(LoggedInMiddleware::class);
    $app->pipe(ServerUrlMiddleware::class);
    $app->pipe(RouteMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(UrlHelperMiddleware::class);
    $app->pipe(DispatchMiddleware::class);
};