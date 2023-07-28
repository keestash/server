<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

use Keestash\Factory\Middleware\ApplicationStartedMiddlewareFactory;
use Keestash\Factory\Middleware\CSPHeaderMiddlewareFactory;
use Keestash\Factory\Middleware\DeactivatedRouteMiddlewareFactory;
use Keestash\Factory\Middleware\DispatchMiddlewareFactory;
use Keestash\Factory\Middleware\EnvironmentMiddlewareFactory;
use Keestash\Factory\Middleware\ExceptionHandlerMiddlewareFactory;
use Keestash\Factory\Middleware\InstanceInstalledMiddlewareFactory;
use Keestash\Factory\Middleware\KeestashHeaderMiddlewareFactory;
use Keestash\Factory\Middleware\PermissionMiddlewareFactory;
use Keestash\Factory\Middleware\RateLimiterMiddlewareFactory;
use Keestash\Factory\Middleware\SanitizeInputMiddlewareFactory;
use Keestash\Factory\Middleware\UserActiveMiddlewareFactory;
use Keestash\Middleware\ApplicationStartedMiddleware;
use Keestash\Middleware\CSPHeaderMiddleware;
use Keestash\Middleware\DeactivatedRouteMiddleware;
use Keestash\Middleware\DispatchMiddleware;
use Keestash\Middleware\EnvironmentMiddleware;
use Keestash\Middleware\ExceptionHandlerMiddleware;
use Keestash\Middleware\InstanceInstalledMiddleware;
use Keestash\Middleware\KeestashHeaderMiddleware;
use Keestash\Middleware\PermissionMiddleware;
use Keestash\Middleware\RateLimiterMiddleware;
use Keestash\Middleware\SanitizeInputMiddleware;
use Keestash\Middleware\UserActiveMiddleware;

return [
    InstanceInstalledMiddleware::class    => InstanceInstalledMiddlewareFactory::class
    , DispatchMiddleware::class           => DispatchMiddlewareFactory::class
    , ApplicationStartedMiddleware::class => ApplicationStartedMiddlewareFactory::class
    , RateLimiterMiddleware::class        => RateLimiterMiddlewareFactory::class
    , PermissionMiddleware::class         => PermissionMiddlewareFactory::class
    , EnvironmentMiddleware::class        => EnvironmentMiddlewareFactory::class
    , CSPHeaderMiddleware::class          => CSPHeaderMiddlewareFactory::class
    , DeactivatedRouteMiddleware::class   => DeactivatedRouteMiddlewareFactory::class
    , SanitizeInputMiddleware::class      => SanitizeInputMiddlewareFactory::class
    , UserActiveMiddleware::class         => UserActiveMiddlewareFactory::class
    , ExceptionHandlerMiddleware::class   => ExceptionHandlerMiddlewareFactory::class
    , KeestashHeaderMiddleware::class     => KeestashHeaderMiddlewareFactory::class
];