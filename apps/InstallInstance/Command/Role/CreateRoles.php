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

namespace KSA\InstallInstance\Command\Role;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Entity\RoleInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\RBAC\NullRole;
use Keestash\Core\DTO\RBAC\Role;
use KSA\InstallInstance\Command\CreateConfig;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRoles extends KeestashCommand {

    public const OPTION_NAME_FORCE = 'force';

    public function __construct(
        private readonly Config                    $config
        , private readonly RBACRepositoryInterface $rbacRepository
        , private readonly LoggerInterface         $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:install:role:create")
            ->setDescription("creates/restores the permissions")
            ->addOption(
                CreateConfig::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE
                , 'whether to force recreation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force = (bool) $input->getOption(CreateRoles::OPTION_NAME_FORCE);
        $roles = $this->config
            ->get(ConfigProvider::PERMISSIONS, new Config([]))
            ->get(ConfigProvider::ROLE_LIST, new Config([]));

        $this->logger->debug('inserting roles', ['count' => $roles->count()]);

        foreach ($roles->toArray() as $id => $name) {
            $roleByName = $this->rbacRepository->getRoleByName($name);
            $roleById   = $this->rbacRepository->getRole($id);

            if (
                false === $force
                && ($this->roleExists($roleByName)
                    || $this->roleExists($roleById))
            ) {
                // TODO implement delete and use here
                $this->logger->info('role exists, skipping',
                    [
                        'roleByName' => json_encode($roleByName)
                        , 'roleById' => json_encode($roleById)
                    ]
                );
                $this->writeInfo(sprintf('role with name %s / id %s exists, skipping', $name, $id), $output);
                continue;
            }

            $this->rbacRepository->createRole(
                new Role(
                    $id
                    , $name
                    , new HashTable() // there are no roles linked with createRole
                    , new DateTimeImmutable()
                )
            );
            $this->logger->info('role inserted',
                [
                    'roleByName' => json_encode($roleByName)
                    , 'roleById' => json_encode($roleById)
                ]
            );
            $this->writeComment(sprintf('role with name %s / id %s inserted', $name, $id), $output);
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function roleExists(RoleInterface $permission): bool {
        return !($permission instanceof NullRole);
    }

}