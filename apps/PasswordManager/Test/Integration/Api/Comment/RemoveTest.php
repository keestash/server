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

use DateTime;
use DateTimeInterface;
use KSA\PasswordManager\Api\Node\Credential\Comment\Remove;
use KSA\PasswordManager\Entity\Comment\Comment;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Core\DTO\User\IUser;
use Ramsey\Uuid\Uuid;

class RemoveTest extends TestCase {

    public function testRemove(): void {
        /** @var Remove $remove */
        $remove = $this->getServiceManager()->get(Remove::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->getServiceManager()->get(CommentRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $comment = $this->getComment(
            new DateTime()
            , $user
            , $edge->getNode()
            , "this is a comment"
        );

        $comment = $commentRepository->addComment($comment);

        $request = $requestService->getVirtualRequestWithToken(
            $user
            , []
            , []
            , ['commentId' => $comment->getId()]
        );

        $response = $remove->handle($request);

        $this->assertTrue(true === $responseService->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testNonExistingComment(): void {
        $this->expectException(PasswordManagerException::class);
        /** @var Remove $remove */
        $remove = $this->getServiceManager()->get(Remove::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);

        $user    = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $request = $requestService->getVirtualRequestWithToken(
            $user
            , []
            , []
            , ['commentId' => 9999]
        );

        $response = $remove->handle($request);
        $this->assertTrue(false === $responseService->isValidResponse($response));
        $this->removeUser($user);
    }

    private function getComment(
        DateTimeInterface $createTs
        , IUser           $user
        , Node            $node
        , string          $string
    ): Comment {
        $comment = new Comment();
        $comment->setCreateTs($createTs);
        $comment->setUser($user);
        $comment->setNode($node);
        $comment->setComment($string);
        return $comment;
    }

}