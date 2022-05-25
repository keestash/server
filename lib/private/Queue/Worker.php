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

namespace Keestash\Queue;

use Keestash\Command\KeestashCommand;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Queue\Handler\IEmailHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Worker extends KeestashCommand {

    private IQueueRepository $queueRepository;
    private IEmailHandler    $emailHandler;

    public function __construct(
        IQueueRepository $queueRepository
        , IEmailHandler  $emailHandler
    ) {
        parent::__construct();

        $this->queueRepository = $queueRepository;
        $this->emailHandler    = $emailHandler;
    }

    protected function configure(): void {
        $this->setName("keestash:worker:run")
            ->setDescription("runs the keestash daemon");
    }


    protected function execute(InputInterface $input, OutputInterface $output): int {
        $execute = true;
        while (true || $execute) {
            $queue = $this->queueRepository->getSchedulableMessages();

            if (0 === $queue->length()) {
                usleep(500000);
                continue;
            }


            /** @var IMessage $q */
            foreach ($queue as $q) {
                $result = 0;
                switch ($q->getType()) {
                    case IMessage::TYPE_EMAIL:
                        $result = $this->emailHandler->handle($q);
                        break;
                    default:
                        throw new KeestashException();
                }

                switch ($result->getCode()) {
                    case IResult::RETURN_CODE_OK:
                        $this->queueRepository->delete($q);
                        break;
                    case IResult::RETURN_CODE_NOT_OK:
                        $q->setAttempts(
                            $q->getAttempts() + 1
                        );
                        $this->queueRepository->update($q);
                        break;
                    default:
                        throw new KeestashException();
                }

            }
        }
        return 0;
    }

}