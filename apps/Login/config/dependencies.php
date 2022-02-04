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

use KSA\Login\Api\Configuration;
use KSA\Login\Api\Login;
use KSA\Login\Controller\Login as LoginController;
use KSA\Login\Controller\Logout;
use KSA\Login\Factory\Api\ConfigurationFactory;
use KSA\Login\Factory\Api\LoginFactory;
use KSA\Login\Factory\Controller\LogoutFactory;
use KSA\Login\Service\TokenService;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'factories' => [
        // api
        Login::class             => LoginFactory::class
        , Configuration::class   => ConfigurationFactory::class

        // controller
        , Logout::class          => LogoutFactory::class
        , LoginController::class => \KSA\Login\Factory\Controller\LoginFactory::class

        // service
        , TokenService::class    => InvokableFactory::class
    ]
];