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

use KSA\PasswordManager\Api\Node\Share\Share;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Container\ContainerInterface;

class ShareFactory {

    public function __invoke(ContainerInterface $container): Share {
        return new Share(
            $container->get(NodeRepository::class)
            , $container->get(IUserRepository::class)
            , $container->get(NodeService::class)
            , $container->get(ShareService::class)
        );
    }

}
