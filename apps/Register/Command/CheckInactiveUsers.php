<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace KSA\Register\Command;

use DateInterval;
use DateTimeImmutable;
use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\IUserStateService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckInactiveUsers extends KeestashCommand {

    public function __construct(
        private readonly IApiLogRepository    $apiLogRepository,
        private readonly IUserRepository      $userRepository,
        private readonly IUserStateRepository $userStateRepository,
        private readonly IUserStateService    $userStateService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("register:check-inactive-users")
            ->setDescription("checks inactive users");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $users        = $this->userRepository->getAll();
        $sixMonthsAgo = (new DateTimeImmutable())->sub(new DateInterval('P6M'));

        /** @var IUser $user */
        foreach ($users as $user) {
            $userLogs = $this->apiLogRepository->read($user);

            /** @var IAPIRequest $apiLog */
            foreach ($userLogs as $apiLog) {
//                $dateTime = new DateTimeImmutable();
//                $then     = $dateTime->setTimestamp((int) $apiLog->getEnd());
//
//                if ($then >= $sixMonthsAgo) {
//                    continue;
//                }
//
//                $state = $this->userStateService->getState($user);
//                $nextState = $this->userStateService->getNextState($state);
//                $this->userStateService->setState(
//                    new UserState()
//                );
            }

        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
