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

use Keestash\Exception\User\UserNotFoundException;
use KSA\Login\Test\Integration\TestCase;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\User\IUserStateRepository;
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
        $this->expectException(UserNotFoundException::class);
        $command = $this->getCommandTester("login:login");
        $command->setInputs(
            [
                'username'   => Uuid::uuid4()->toString()
                , 'password' => Uuid::uuid4()->toString()
            ]
        );
        $command->execute([]);
    }

    public function testWithDisabledUser(): void {
        $this->expectException(UserNotFoundException::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        $username            = Uuid::uuid4()->toString();
        $password            = Uuid::uuid4()->toString();
        $user                = $this->createUser(
            $username
            , $password
        );

        $userStateRepository->lock($user);
        $command = $this->getCommandTester("login:login");
        $command->setInputs(
            [
                'username'   => $username
                , 'password' => $password
            ]
        );
        $command->execute([]);
        $this->removeUser($user);
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