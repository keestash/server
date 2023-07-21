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

namespace Keestash\Command\Worker;

use Keestash\Command\KeestashCommand;
use Keestash\Exception\KeestashException;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\Core\Data\IDataService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerLocker extends KeestashCommand {

    public const ARGUMENT_NAME_MODE = 'mode';

    public function __construct(
        private readonly IDataService $dataService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("worker:lock")
            ->setDescription("locks or unlocks the worker")
            ->addArgument(
                WorkerLocker::ARGUMENT_NAME_MODE
                , InputArgument::REQUIRED
                , '"lock" or "unlock"'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $mode          = (string) $input->getArgument(WorkerLocker::ARGUMENT_NAME_MODE);
        $workerLogFile = $this->dataService->getPath() . '/' . WorkerRunner::WORKER_LOG_FILE;

        if ($mode === 'lock') {
            $this->lock($workerLogFile, $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        if ($mode === 'unlock') {
            $this->unlock($workerLogFile, $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }
        throw new KeestashException();
    }

    private function lock(string $path, OutputInterface $output): void {
        if (true === is_file($path)) {
            $this->writeInfo('Already locked. Doing nothing', $output);
            return;
        }
        $result = file_put_contents($path, (string) time());
        $this->writeInfo(
            sprintf(
                'lock file created. Result %s',
                $result
            )
            , $output
        );
    }

    private function unlock(string $path, OutputInterface $output): void {
        if (false === is_file($path)) {
            $this->writeInfo('no file found to delete. Doing nothing', $output);
            return;
        }
        $result = unlink($path);
        $this->writeInfo(
            sprintf(
                'lock file deleted. Result %s',
                $result
            )
            , $output
        );
    }

}