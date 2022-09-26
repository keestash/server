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

namespace KSA\InstallInstance\Command;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DemoMode extends KeestashCommand {

    private InstanceDB             $instanceDb;
    private IUserService           $userService;
    private IUserRepository        $userRepository;
    private Config                 $config;
    private IUserRepositoryService $userRepositoryService;

    public function __construct(
        InstanceDB               $instanceDB
        , IUserService           $userService
        , IUserRepository        $userRepository
        , Config                 $config
        , IUserRepositoryService $userRepositoryService
    ) {
        parent::__construct();
        $this->instanceDb            = $instanceDB;
        $this->userService           = $userService;
        $this->userRepository        = $userRepository;
        $this->config                = $config;
        $this->userRepositoryService = $userRepositoryService;
    }

    protected function configure(): void {
        $this->setName("instance:demomode")
            ->setDescription("turns the demo mode on");
    }

    private function enable(string $path): bool {
        $put     = @file_put_contents(
            $path
            , (string) getmypid()
        );
        $enabled = $put !== false;

        if (true === $enabled) {
            $this->userRepositoryService->createUser(
                $this->userService->getDemoUser()
            );
            $this->instanceDb->addOption("demo", "true");
        }
        return $enabled;
    }

    private function disable(string $path): bool {
        if (false === $this->isDemoMode($path)) return true;
        $disabled = @unlink($path);

        if (true === $disabled) {
            $demoUser = $this->userRepository->getUser(IUser::DEMO_USER_NAME);
            $this->userRepositoryService->removeUser($demoUser);
            $this->instanceDb->removeOption("demo");
        }
        return $disabled;
    }

    private function isDemoMode(string $path): bool {
        return true === is_file($path);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $dataRoot     = (string) $this->config->get(ConfigProvider::DATA_PATH);
        $dataRoot     = realpath($dataRoot);
        $demoModePath = $dataRoot . "/.mode.demo";

        if (false === $dataRoot) {
            throw new InstallInstanceException();
        }

        $ran = false;
        if ($this->isDemoMode($demoModePath)) {
            $output->writeln("disabling demo mode");
            $ran = $this->disable($demoModePath);
        } else {
            $output->writeln("enabling demo mode");
            $ran = $this->enable($demoModePath);
        }

        if (false === $ran) {
            throw new InstallInstanceException();
        }
        $output->writeln("demo mode switched");
        return 0;
    }

}