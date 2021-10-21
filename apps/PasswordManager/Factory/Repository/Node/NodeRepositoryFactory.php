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

namespace KSA\PasswordManager\Factory\Repository\Node;

use doganoo\DI\DateTime\IDateTimeService;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\HTTP\IJWTService;
use Psr\Container\ContainerInterface;

class NodeRepositoryFactory {

    public function __invoke(ContainerInterface $container): NodeRepository {
        return new NodeRepository(
            $container->get(IBackend::class)
            , $container->get(IUserRepository::class)
            , $container->get(PublicShareRepository::class)
            , $container->get(IDateTimeService::class)
            , $container->get(ILogger::class)
            , $container->get(EncryptionService::class)
            , $container->get(IKeyService::class)
            , $container->get(IOrganizationRepository::class)
            , $container->get(IJWTService::class)
        );
    }

}