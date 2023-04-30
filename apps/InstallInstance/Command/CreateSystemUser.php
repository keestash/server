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

namespace KSA\InstallInstance\Command;

use Keestash\Command\KeestashCommand;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\IFileService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSystemUser extends KeestashCommand {

    public const OPTION_NAME_FORCE = 'force';

    public function __construct(
        private readonly IUserRepositoryService $userRepositoryService
        , private readonly IUserService         $userService
        , private readonly IFileService         $fileService
        , private readonly IFileRepository      $fileRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:install:system-user")
            ->setDescription("creates the system user")
            ->addOption(
                CreateSystemUser::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE
                , 'whether to force recreation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force         = (bool) $input->getOption(CreateSystemUser::OPTION_NAME_FORCE);
        $systemUser    = $this->userService->getSystemUser();
        $hasSystemUser = $this->userRepositoryService->userExistsById(
            (string) $systemUser->getId()
        );

        if (true === $hasSystemUser && false === $force) {
            // TODO ask for override
            throw new InstallInstanceException();
        }

        $this->userRepositoryService->createSystemUser($systemUser);
        $defaultImage = $this->fileService->getDefaultImage();
        $defaultImage->setOwner($systemUser);
        $this->fileRepository->add($defaultImage);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}