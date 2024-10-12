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

namespace KSA\Login\Test\Integration\Command;

use DateTimeImmutable;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\DTO\User\UserStateName;
use KSA\Login\Test\Integration\TestCase;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\User\IUserStateService;
use Ramsey\Uuid\Uuid;

class LoginTest extends TestCase {

    public function testLogin(): void {
        $username = Uuid::uuid4()->toString();
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            $username
            , $password
        );
        $command  = $this->getCommandTester("login:login");
        $command->setInputs(
            [
                'username'   => $username
                , 'password' => $password
            ]
        );
        $command->execute([]);
        $this->assertTrue(IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL === $command->getStatusCode());
        $this->removeUser($user);
    }

    public function testNonExistingUser(): void {
        $command = $this->getCommandTester("login:login");
        $command->setInputs(
            [
                'username'   => Uuid::uuid4()->toString()
                , 'password' => Uuid::uuid4()->toString()
            ]
        );
        $result = $command->execute([]);
        $this->assertTrue($result === IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL);
    }

    public function testWithDisabledUser(): void {
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);
        $username         = Uuid::uuid4()->toString();
        $password         = Uuid::uuid4()->toString();
        $user             = $this->createUser(
            $username
            , $password
        );

        $userStateService->setState(
            new UserState(
                0,
                $user,
                UserStateName::LOCK_CANDIDATE_STAGE_ONE,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );

        $userStateService->setState(
            new UserState(
                0,
                $user,
                UserStateName::LOCK_CANDIDATE_STAGE_TWO,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );

        $userStateService->setState(
            new UserState(
                0,
                $user,
                UserStateName::LOCK,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );

        $command = $this->getCommandTester("login:login");
        $command->setInputs(
            [
                'username'   => $username
                , 'password' => $password
            ]
        );
        $result = $command->execute([]);
        $this->removeUser($user);
        $this->assertTrue($result === IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL);
    }

    public function testWithInvalidCredentials(): void {
        $username = Uuid::uuid4()->toString();
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            $username
            , $password
        );

        $command = $this->getCommandTester("login:login");
        $command->setInputs(
            [
                'username'   => $username
                , 'password' => Uuid::uuid4()->toString()
            ]
        );
        $command->execute([]);
        $this->assertTrue(IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL === $command->getStatusCode());
        $this->assertTrue(str_contains($command->getDisplay(), 'Invalid credentials'));
        $this->removeUser($user);
    }

}
