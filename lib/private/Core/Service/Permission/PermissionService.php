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

namespace Keestash\Core\Service\Permission;

use DateTimeImmutable;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\RBAC\NullPermission;
use Keestash\Core\DTO\RBAC\Permission;
use KSP\Core\Service\Permission\IPermissionService;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface;

class PermissionService implements IPermissionService {

    public function __construct(
        private readonly Config                    $config
        , private readonly LoggerInterface         $logger
        , private readonly RBACRepositoryInterface $rbacRepository
    ) {
    }

    #[\Override]
    public function recreatePermissions(): void {
        $this->rbacRepository->clearPermissions();
        $this->createPermissions();
    }

    #[\Override]
    public function createPermissions(): void {
        /** @var Config $permissions */
        $permissions = $this->config
            ->get(ConfigProvider::PERMISSIONS, new Config([]))
            ->get(ConfigProvider::PERMISSION_LIST, new Config([]));

        $this->logger->debug('inserting permissions', ['count' => $permissions->count()]);

        foreach ($permissions->toArray() as $id => $name) {
            $permissionByName = $this->rbacRepository->getPermissionByName($name);
            $permissionById   = $this->rbacRepository->getPermission($id);

            if (
                ($this->permissionExists($permissionByName)
                    || $this->permissionExists($permissionById))
            ) {
                $this->logger->info('permission exists, skipping',
                    [
                        'permissionByName' => json_encode($permissionByName)
                        , 'permissionById' => json_encode($permissionById)
                    ]
                );
                $this->logger->debug(sprintf('permission with name %s / id %s exists, skipping', $name, $id));
                continue;
            }

            $this->rbacRepository->createPermission(
                new Permission(
                    $id
                    , $name
                    , new DateTimeImmutable()
                )
            );
            $this->logger->info('permission inserted',
                [
                    'permissionByName' => json_encode($permissionByName)
                    , 'permissionById' => json_encode($permissionById)
                ]
            );
            $this->logger->debug((sprintf('permission with name %s / id %s inserted', $name, $id)));
        }
    }

    private function permissionExists(PermissionInterface $permission): bool {
        return !($permission instanceof NullPermission);
    }

}