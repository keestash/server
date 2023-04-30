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

namespace KSA\InstallInstance\Command\Permission;

use DateTimeImmutable;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\RBAC\NullPermission;
use Keestash\Core\DTO\RBAC\Permission;
use KSA\InstallInstance\Command\CreateConfig;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePermissions extends KeestashCommand {

    public const OPTION_NAME_FORCE = 'force';

    public function __construct(
        private readonly Config                    $config
        , private readonly RBACRepositoryInterface $rbacRepository
        , private readonly LoggerInterface         $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:install:permission:create")
            ->setDescription("creates/restores the permissions")
            ->addOption(
                CreateConfig::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE
                , 'whether to force recreation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force = (bool) $input->getOption(CreatePermissions::OPTION_NAME_FORCE);
        /** @var Config $permissions */
        $permissions = $this->config
            ->get(ConfigProvider::PERMISSIONS, new Config([]))
            ->get(ConfigProvider::PERMISSION_LIST, new Config([]));

        $this->logger->debug('inserting permissions', ['count' => $permissions->count()]);

        foreach ($permissions->toArray() as $id => $name) {
            $permissionByName = $this->rbacRepository->getPermissionByName($name);
            $permissionById   = $this->rbacRepository->getPermission($id);

            if (
                false === $force
                && ($this->permissionExists($permissionByName)
                    || $this->permissionExists($permissionById))
            ) {
                // TODO implement delete and use here
                $this->logger->info('permission exists, skipping',
                    [
                        'permissionByName' => json_encode($permissionByName)
                        , 'permissionById' => json_encode($permissionById)
                    ]
                );
                $this->writeComment(sprintf('permission with name %s / id %s exists, skipping', $name, $id), $output);
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
            $this->writeInfo(sprintf('permission with name %s / id %s inserted', $name, $id), $output);
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function permissionExists(PermissionInterface $permission): bool {
        return !($permission instanceof NullPermission);
    }

}