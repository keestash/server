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
use KSA\PasswordManager\Api\Comment\Remove;
use KSA\PasswordManager\Entity\Comment\Comment;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Core\DTO\User\IUser;
use KST\TestCase;

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
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        $credential = $this->getCredential();
        $user       = $this->getUser();
        $edge       = $credentialService->insertCredential(
            $credential
            , $nodeRepository->getRootForUser($user)
        );

        $comment = $this->getComment(
            new DateTime()
            , $this->getUser()
            , $edge->getNode()
            , "this is a comment"
        );

        $comment = $commentRepository->addComment($comment);

        $request = $requestService->getRequestWithToken(
            $this->getUser()
            , []
            , []
            , ['commentId' => $comment->getId()]
        );

        $response = $remove->handle($request);

        $this->assertTrue(true === $responseService->isValidResponse($response));

    }

    public function testNonExistingComment():void {
        $this->expectException(PasswordManagerException::class);
        /** @var Remove $remove */
        $remove = $this->getServiceManager()->get(Remove::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);

        $request = $requestService->getRequestWithToken(
            $this->getUser()
            , []
            , []
            , ['commentId' => 9999]
        );

        $response = $remove->handle($request);
        $this->assertTrue(false === $responseService->isValidResponse($response));
    }

    private function getCredential(): Credential {
        $password = "mySuperSecurePassword";
        $url      = "keestash.com";
        $userName = "keestashSystemUser";
        $title    = "organization.keestash.com";

        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        return $credentialService->createCredential(
            $password
            , $url
            , $userName
            , $title
            , $this->getUser()
        );
    }

    private function getComment(
        DateTimeInterface $createTs
        , IUser $user
        , Node $node
        , string $string
    ): Comment {
        $comment = new Comment();
        $comment->setCreateTs($createTs);
        $comment->setUser($user);
        $comment->setNode($node);
        $comment->setComment($string);
        return $comment;
    }

}