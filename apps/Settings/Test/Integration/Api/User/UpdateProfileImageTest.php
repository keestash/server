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

namespace KSA\Settings\Test\Integration\Api\User;

use KSA\Settings\ConfigProvider;
use KSA\Settings\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Laminas\Diactoros\UploadedFile;
use Ramsey\Uuid\Uuid;

class UpdateProfileImageTest extends TestCase {

    public function testUpload(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::USER_PROFILE_IMAGE_UPDATE
                    , [
                        'user_hash' => $user->getHash()
                    ]
                    , $user
                    , $headers
                    , [new UploadedFile(
                           fopen("file:///" . __DIR__ . '/keestash.png', 'r')
                           , 0
                           , 0
                       )]
                )
            );

        $this->assertStatusCode(IResponse::OK, $response);
        $this->removeUser($user);
    }

}