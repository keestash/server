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

namespace KSA\Register\Factory\Api;

use KSA\Register\Api\User\ResetPassword;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ResetPasswordFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): ResetPassword {
        return new ResetPassword(
            $container->get(IUserService::class)
            , $container->get(IUserStateRepository::class)
            , $container->get(IUserRepository::class)
            , $container->get(LoggerInterface::class)
            , $container->get(IEventService::class)
            , $container->get(IResponseService::class)
            , $container->get(ICollectorService::class)
            , $container->get(IUserStateService::class)
        );
    }

}
