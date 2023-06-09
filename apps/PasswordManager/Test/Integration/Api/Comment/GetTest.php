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

namespace KSA\PasswordManager\Test\Integration\Api\Comment;

use KSA\PasswordManager\Api\Node\Credential\Comment\Get;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use Ramsey\Uuid\Uuid;

class GetTest extends TestCase {

    public function getData(): array {
        return [
            [2, true]
            , [99999, false]
        ];
    }

    /**
     * @throws CommentException
     * @dataProvider getData
     *
     * TODO add a test where the user does not own the node/
     *  does not belong to the organization
     */
    public function testGet(int $nodeId, bool $isValid): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $request  = $requestService->getVirtualRequestWithToken($user);
        $request  = $request->withAttribute('nodeId', $nodeId)
            ->withAttribute("sortField", "id")
            ->withAttribute("sortDirection", "ASC");
        $response = $get->handle($request);

        $this->assertTrue($isValid === $responseService->isValidResponse($response));
    }

}