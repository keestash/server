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

namespace KSA\PasswordManager\Factory\Api\Node\Credential;

use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CreateFactory {

    public function __invoke(ContainerInterface $container): Create {
        return new Create(
            $container->get(NodeRepository::class)
            , $container->get(CredentialService::class)
            , $container->get(LoggerInterface::class)
            , $container->get(NodeEncryptionService::class)
            , $container->get(AccessService::class)
            , $container->get(IActivityService::class)
        );
    }

}