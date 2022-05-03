<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Comment;

use DateTime;
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use KSA\PasswordManager\Entity\Comment\Comment;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Exception\Node\Comment\CommentRepositoryException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\HTTP\IJWTService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;
    private ILogger           $logger;
    private IJWTService       $jwtService;

    public function __construct(
        CommentRepository $commentRepository
        , NodeRepository  $nodeRepository
        , ILogger         $logger
        , IJWTService     $jwtService
    ) {
        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->logger            = $logger;
        $this->jwtService        = $jwtService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws CommentException
     * @throws CommentRepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters    = (array) $request->getParsedBody();
        $commentString = $parameters['comment'] ?? null;
        $nodeId        = $parameters['node_id'] ?? null;

        if (null === $commentString || "" === trim($commentString)) {
            throw new CommentException();
        }

        if (null === $nodeId) {
            throw new CommentException();
        }

        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            $this->logger->error($exception->getMessage());
            return new JsonResponse('error while retrieving node', IResponse::INTERNAL_SERVER_ERROR);
        }

        $comment = new Comment();
        $comment->setComment($commentString);
        $comment->setCreateTs(new DateTime());
        $comment->setNode($node);
        $comment->setUser($token->getUser());
        $comment->setJWT(
            $this->jwtService->getJWT(
                new Audience(
                    IAudience::TYPE_ASSET
                    , 'default'
                )
            )
        );
        $comment = $this->commentRepository->addComment($comment);
        $node->setUpdateTs(new DateTime());
        $this->nodeRepository->updateCredential($node);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "comment" => $comment
            ]
        );

    }

}
