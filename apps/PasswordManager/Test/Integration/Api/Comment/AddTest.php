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

use KSA\PasswordManager\Api\Comment\Add;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KST\TestCase;

/**
 * Class AddTest
 * @package KSA\PasswordManager\Test\Integration\Api\Comment
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class AddTest extends TestCase {

    public function getData(): array {
        return [
            [["comment" => "test", 'node_id' => 2], null, true]
            , [["comment" => null, 'node_id' => 2], CommentException::class, true]
            , [['node_id' => 2], CommentException::class, true]
            , [["comment" => "test", 'node_id' => null], CommentException::class, true]
            , [["comment" => "test", 'node_id' => 9], null, false]
            , [["comment" => "test"], CommentException::class, true]
            , [[], CommentException::class, true]
        ];
    }

    /**
     * @param array       $parameters
     * @param string|null $exception
     * @param bool        $isValid
     * @throws CommentException
     * @dataProvider getData
     *
     * TODO add a test where the user does not own the node/
     *  does not belong to the organization
     */
    public function testAll(
        array     $parameters
        , ?string $exception
        , bool    $isValid
    ): void {
        /** @var Add $add */
        $add = $this->getServiceManager()->get(Add::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);

        if (null !== $exception) {
            $this->expectException($exception);
        }
        $serverRequest = $requestService->getRequestWithToken(
            $this->getUser()
            , []
            , []
            , $parameters
            , []
            , []
        );

        $response = $add->handle($serverRequest);
        $this->assertTrue($isValid === $responseService->isValidResponse($response));
    }

//    public function testWithXSS(): void {
//        $comment = '<body onload=alert(\'test1\')>The body</body>';
//
//        /** @var Add $add */
//        $add = $this->getServiceManager()->get(Add::class);
//        /** @var RequestService $requestService */
//        $requestService = $this->getServiceManager()->get(RequestService::class);
//        /** @var CommentRepository $commentRepository */
//        $commentRepository = $this->getServiceManager()->get(CommentRepository::class);
//
//        $serverRequest = $requestService->getRequestWithToken(
//            $this->getUser()
//            , []
//            , []
//            , ['comment' => $comment, 'node_id' => 2]
//            , []
//            , []
//        );
//        $response      = $add->handle($serverRequest);
//        $data = json_decode((string)$response->getBody(), true);
//        $c  = $commentRepository->getCommentById($data['comment']['id']);
//        dump($c->getComment());exit();
//    }

}