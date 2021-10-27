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

namespace Keestash\Factory\Core\Service\Encryption\Key;

use Keestash\Core\Service\Encryption\Key\KeyService;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use Psr\Container\ContainerInterface;

class KeyServiceFactory {

    public function __invoke(ContainerInterface $container): IKeyService {
        return new KeyService(
            $container->get(IUserKeyRepository::class)
            , $container->get(IEncryptionService::class)
            , $container->get(IOrganizationKeyRepository::class)
            , $container->get(ICredentialService::class)
        );
    }

}