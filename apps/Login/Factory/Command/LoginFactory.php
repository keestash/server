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

namespace KSA\Login\Factory\Command;

use KSA\Login\Command\Login;
use KSA\Login\Service\TokenService;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\LDAP\ILDAPService;
use KSP\Core\Service\User\IUserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LoginFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): Login {
        return new Login(
            $container->get(IUserRepository::class)
            , $container->get(LoggerInterface::class)
            , $container->get(IUserService::class)
            , $container->get(ILDAPService::class)
            , $container->get(IConnectionRepository::class)
            , $container->get(TokenService::class)
            , $container->get(ITokenRepository::class)
            , $container->get(IDerivationRepository::class)
            , $container->get(IDerivationService::class)
        );
    }

}