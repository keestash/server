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

namespace KSA\PasswordManager\Factory\Service\Node\BreadCrumbService;

use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSP\Core\Cache\ICacheService;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;
use Psr\Container\ContainerInterface;

class BreadCrumbServiceFactory {

    public function __invoke(ContainerInterface $container): BreadCrumbService {
        return new BreadCrumbService(
            $container->get(ICacheService::class)
            , $container->get(NodeRepository::class)
            , $container->get(IL10N::class)
            , $container->get(ILogger::class)
        );
    }

}