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
use Keestash\Core\DTO\Queue\Result;
use Keestash\Event\Worker\MessageProcessedEvent;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Queue\Handler\IEmailHandler;
use KSP\Queue\Handler\IEventHandler;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Worker extends KeestashCommand {

    private IQueueService    $queueService;
    private IEmailHandler    $emailHandler;
    private ILogger          $logger;
    private IEventManager    $eventManager;
    private Config           $config;
    private IQueueRepository $queueRepository;
    private IEventHandler    $eventHandler;

    public function __construct(
        IQueueService      $queueService
        , IEmailHandler    $emailHandler
        , ILogger          $logger
        , IEventManager    $eventManager
        , Config           $config
        , IQueueRepository $queueRepository
        , IEventHandler    $eventHandler
    ) {
        parent::__construct();

        $this->queueService    = $queueService;
        $this->emailHandler    = $emailHandler;
        $this->logger          = $logger;
        $this->eventManager    = $eventManager;
        $this->config          = $config;
        $this->queueRepository = $queueRepository;
        $this->eventHandler    = $eventHandler;
    }

    protected function configure(): void {
        $this->setName("keestash:worker:run")
            ->setDescription("runs the keestash daemon");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $execute = true;
        while (true || $execute) {
            $queue = $this->queueService->prepareQueue(
//                (bool) $this->config->get("debug", false)
                true
            );

            if (0 === $queue->length()) {
                usleep(500000);
                continue;
            }

            $this->logger->debug("processing {$queue->length()} messages");

            /** @var IMessage $message */
            foreach ($queue as $message) {
                $result = Result::getNotOk();

                try {

                    switch ($message->getType()) {
                        case IMessage::TYPE_EMAIL:
                            $this->logger->debug('handling an email message');
                            $result = $this->emailHandler->handle($message);
                            break;
                        case IMessage::TYPE_EVENT:
                            $this->logger->debug('handling an event message');
                            $result = $this->eventHandler->handle($message);
                            break;
                        default:
                            throw new KeestashException();
                    }

                } catch (Throwable $exception) {
                    $this->logger->error('error processing message', ['exception' => $exception]);
                }

                $this->logger->debug('result code: ' . $result->getCode());
                switch ($result->getCode()) {
                    case IResult::RETURN_CODE_OK:
                        $this->queueRepository->delete($message);
                        break;
                    case IResult::RETURN_CODE_NOT_OK:
                        $this->updateAttempts($message);
                        break;
                    default:
                        throw new KeestashException();
                }

//                $this->eventManager->execute(
//                    new MessageProcessedEvent(
//                        $message
//                        , $result
//                    )
//                );

            }
        }
        return 0;
    }

    private function updateAttempts(IMessage $message): void {
        $message->setAttempts(
            $message->getAttempts() + 1
        );
        $this->queueRepository->update($message);
    }

}