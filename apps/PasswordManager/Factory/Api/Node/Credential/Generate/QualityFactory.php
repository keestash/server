<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\PasswordManager\Factory\Api\Node\Credential\Generate;

use Interop\Container\ContainerInterface;
use KSA\PasswordManager\Api\Node\Credential\Generate\Quality;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KSP\Core\Service\HTTP\IResponseService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Log\LoggerInterface;

class QualityFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null): Quality {
        return new Quality(
            $container->get(IPasswordService::class)
            , $container->get(LoggerInterface::class)
            , $container->get(IResponseService::class)
        );
    }

}