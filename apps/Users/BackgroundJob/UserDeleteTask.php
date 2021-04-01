<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\Users\BackgroundJob;

use DateTime;
use doganoo\Backgrounder\Task\Task;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\User\Event\UserPreRemovedEvent;
use Keestash\Core\Service\User\Event\UserRemovedEvent;
use Keestash\Core\Service\User\UserService;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserStateRepository;

/**
 * Class UserDeleteJob
 * @package Keestash\BackgroundJob\Task
 */
class UserDeleteTask extends Task {

    private UserService          $userService;
    private HashTable            $userList;
    private ConfigService        $configService;
    private IUserStateRepository $userStateRepository;
    private ILogger              $logger;

    public function __construct(
        UserService $userService
        , HashTable $userList
        , ConfigService $configService
        , IUserStateRepository $userStateRepository
        , ILogger $logger
    ) {
        $this->userService         = $userService;
        $this->userList            = $userList;
        $this->configService       = $configService;
        $this->userStateRepository = $userStateRepository;
        $this->logger              = $logger;
    }

    protected function onAction(): void {

    }

    protected function action(): bool {

        $daysAfterDelete = $this->configService->getValue("user_delete_delay", 90);

        foreach ($this->userList->keySet() as $key) {
            /** @var IUserState $userState */
            $userState = $this->userList->get($key);

            if ($userState->getValidFrom()->diff(new DateTime())->days < $daysAfterDelete) {
                $this->logger->info("{$userState->getUser()->getId()} is not ready to delete yet");
                continue;
            }

            Keestash::getServer()
                ->getEventManager()
                ->execute(
                    new UserPreRemovedEvent($userState->getUser())
                );

            $response = $removed = $this->userService->removeUser($userState->getUser());
            $removed  = true === $response["success"];

            if (true === $removed) {
                $removedAll = $this->userStateRepository->removeAll($userState->getUser());
                $this->logger->info("userstate removed: " . $removedAll);
            }

            Keestash::getServer()
                ->getEventManager()
                ->execute(
                    new UserRemovedEvent($userState->getUser(), $removed)
                );

        }

        return true;
    }

    protected function onClose(): void {

    }

}
