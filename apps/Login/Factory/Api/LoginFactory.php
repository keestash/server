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

namespace KSA\Login\Factory\Api;

use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\User\UserService;
use KSA\Login\Api\Login;
use KSA\Login\Service\TokenService;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\L10N\IL10N;
use Psr\Container\ContainerInterface;

class LoginFactory {

    public function __invoke(ContainerInterface $container): Login {
        return new Login(
            $container->get(IUserRepository::class)
            , $container->get(IL10N::class)
            , $container->get(UserService::class)
            , $container->get(ITokenRepository::class)
            , $container->get(TokenService::class)
            , $container->get(ILocaleService::class)
            , $container->get(ILanguageService::class)
            , $container->get(InstanceDB::class)
            , $container->get(IJWTService::class)
        );
    }

}