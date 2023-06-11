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

use doganoo\DIP\Object\String\StringService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Application;
use KSA\Register\Api\User\Add;
use KSA\Settings\Service\ISettingsService;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class AddFactory {

    public function __invoke(ContainerInterface $container): Add {
        return new Add(
            $container->get(UserService::class)
            , $container->get(LoggerInterface::class)
            , $container->get(IUserRepositoryService::class)
            , $container->get(StringService::class)
            , $container->get(IPaymentService::class)
            , $container->get(IPaymentLogRepository::class)
            , $container->get(Application::class)
            , $container->get(IConfigService::class)
            , $container->get(IEventService::class)
            , $container->get(ISettingsService::class)
        );
    }

}