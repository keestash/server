<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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
use KSP\Command\IKeestashCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Ping extends KeestashCommand {

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        parent::__construct();
        $this->logger = $logger;
    }

    protected function configure(): void {
        $this->setName("instance:ping")
            ->setDescription("pings the app");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $data = json_encode([(string) time()]);
        $this->writeInfo($data, $output);
        $this->logger->error($data);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}