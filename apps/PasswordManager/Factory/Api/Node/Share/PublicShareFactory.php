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

namespace KSA\PasswordManager\Factory\Api\Node\Share;

use KSA\PasswordManager\Api\Node\Share\Public\PublicShare;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Core\Service\HTTP\IResponseService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class PublicShareFactory {

    public function __invoke(ContainerInterface $container): PublicShare {
        return new PublicShare(
            $container->get(NodeRepository::class)
            , $container->get(ShareService::class)
            , $container->get(PublicShareRepository::class)
            , $container->get(LoggerInterface::class)
            , $container->get(IResponseService::class)
            , $container->get(AccessService::class)
        );
    }

}
