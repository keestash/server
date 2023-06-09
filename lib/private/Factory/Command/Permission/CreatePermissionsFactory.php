<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Factory\Command\Permission;

use Keestash\Command\Permission\CreatePermissions;
use KSP\Core\Service\Permission\IPermissionService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreatePermissionsFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): CreatePermissions {
        return new CreatePermissions(
            $container->get(IPermissionService::class)
        );
    }

}