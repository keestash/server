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
use Keestash\Core\Service\Instance\InstallerService;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Command\IKeestashCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstanceData extends KeestashCommand {

    public function __construct(
        private readonly InstallerService $installerService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:install:instance-data")
            ->setDescription("creates the instance-data");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $hasIdAndHash = $this->installerService->hasIdAndHash();
        if (true === $hasIdAndHash) {
            throw new InstallInstanceException('id and hash exists. Can not override, this breaks encryption');
        }

        $written = $this->installerService->writeIdAndHash();
        if (false === $written) {
            throw new InstallInstanceException();
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}