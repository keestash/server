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

namespace KSA\PasswordManager\Test\Integration\Api\Credential\AdditionalData;

use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class AddTest extends TestCase {

    public function testAddAdditionalData(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $credential = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::POST
                , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_ADD
                , [
                    'credentialId' => $credential->getNode()->getId()
                    , 'key'        => Uuid::uuid4()->toString()
                    , 'value'      => Uuid::uuid4()->toString()
                ]
                , $user
                , $headers
            )
        );

        $this->assertStatusCode(IResponse::CREATED, $response);
        $this->logout($headers, $user);
    }

    public function testAddEmpty(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $headers  = $this->login($user, $password);
        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::POST
                , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_ADD
                , []
                , $user
                , $headers
            )
        );
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testMissingCredential(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $headers  = $this->login($user, $password);
        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::POST
                , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_ADD
                , [
                    'credentialId' => 999999999999
                ]
                , $user
                , $headers
            )
        );
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

}