<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Integration\Api\Generate;

use KSA\PasswordManager\Api\Node\Credential\Generate\Generate;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use Ramsey\Uuid\Uuid;

class GenerateTest extends TestCase {

    /**
     * @dataProvider provideData
     */
    public function testGenerate(array $attributes, bool $valid): void {
        /** @var Generate $generate */
        $generate = $this->getServiceManager()->get(Generate::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);

        $user    = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $request = $requestService->getVirtualRequestWithToken($user);

        foreach ($attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $response = $generate->handle($request);
        $this->assertTrue($valid === $responseService->isValidResponse($response));
        $this->removeUser($user);
    }

    // TODO add more cases
    public function provideData(): array {
        return [
            [['length' => "8", 'upperCase' => "true", 'lowerCase' => "true", "digit" => "true", "specialChars" => "true"], true]
            , [['length' => null, 'upperCase' => "true", 'lowerCase' => "true", "digit" => "true", "specialChars" => "true"], false]
            , [['length' => "8", 'upperCase' => "false", 'lowerCase' => "true", "digit" => "true", "specialChars" => "true"], true]
            , [['length' => "8", 'upperCase' => "false", 'lowerCase' => "false", "digit" => "true", "specialChars" => "true"], true]
            , [['length' => "8", 'upperCase' => "false", 'lowerCase' => "false", "digit" => "false", "specialChars" => "true"], true]
            , [['length' => "8", 'upperCase' => "false", 'lowerCase' => "false", "digit" => "false", "specialChars" => "false"], true]
            , [['length' => "8", 'upperCase' => "false", 'lowerCase' => "false", "digit" => "false", "specialChars" => "false"], true]
            , [['length' => "8", 'upperCase' => "false", 'lowerCase' => "false", "digit" => 1235, "specialChars" => false], false]
        ];
    }

}