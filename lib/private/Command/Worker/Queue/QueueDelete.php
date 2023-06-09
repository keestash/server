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

namespace Keestash\Command\Worker\Queue;

use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\Queue\IQueueService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueDelete extends KeestashCommand {

    public const ARGUMENT_NAME_UUID = 'uuid';
    private IQueueService $queueService;

    public function __construct(IQueueService $queueService) {
        parent::__construct();
        $this->queueService = $queueService;
    }

    protected function configure(): void {
        $this->setName("keestash:worker:queue:delete")
            ->setDescription("deletes a single message")
            ->addArgument(
                QueueDelete::ARGUMENT_NAME_UUID
                , InputArgument::REQUIRED
                , 'the uuid to delete'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $uuid = (string) $input->getArgument(QueueDelete::ARGUMENT_NAME_UUID);
        $this->queueService->remove($uuid);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}