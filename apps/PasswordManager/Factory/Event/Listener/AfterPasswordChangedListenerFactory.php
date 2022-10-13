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

namespace KSA\PasswordManager\Factory\Event\Listener;

use Keestash\Core\Service\Encryption\Credential\CredentialService;
use KSA\PasswordManager\Event\Listener\AfterPasswordChanged;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use Psr\Log\LoggerInterface as ILogger;
use Psr\Container\ContainerInterface;

class AfterPasswordChangedListenerFactory {

    public function __invoke(ContainerInterface $container): AfterPasswordChanged {
        return new AfterPasswordChanged(
            $container->get(IUserKeyRepository::class)
            , $container->get(IEncryptionService::class)
            , $container->get(CredentialService::class)
            , $container->get(ILogger::class)
        );
    }

}