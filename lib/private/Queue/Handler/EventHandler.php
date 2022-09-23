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

namespace Keestash\Queue\Handler;

use Exception;
use Keestash\Core\DTO\Queue\Result;
use KSP\Core\DTO\Queue\IEventMessage;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEvent;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Queue\Handler\IEventHandler;
use Laminas\Serializer\Adapter\PhpSerialize;
use Psr\Container\ContainerInterface;

class EventHandler implements IEventHandler {

    private ContainerInterface $container;
    private ILogger            $logger;

    public function __construct(
        ContainerInterface $container
        , ILogger          $logger
    ) {
        $this->container = $container;
        $this->logger    = $logger;
    }

    public function handle(IMessage $message): IResult {

        // todo implement rate limiting

        if (!($message instanceof IEventMessage)) {
            throw new Exception();
        }
        $serializer = new PhpSerialize();
        $payload    = $message->getPayload();
        $listener   = $payload['listener'];
        $serialized = $payload['event']['serialized'];

        $listenerObject = $this->container->get($listener);

        $executed = false;
        if ($listenerObject instanceof IListener) {

            $event = $serializer->unserialize($serialized);

            if ($event instanceof IEvent) {

                try {
                    $listenerObject->execute($event);
                    $executed = true;
                } catch (Exception $exception) {
                    $this->logger->error(
                        'error while handling event'
                        , [
                            'message'     => $message
                            , 'exception' => $exception
                        ]
                    );
                }
            }
        }

        $result = new Result();
        $result->setCode(
            true === $executed
                ? IResult::RETURN_CODE_OK
                : IResult::RETURN_CODE_NOT_OK
        );
        return $result;
    }

}