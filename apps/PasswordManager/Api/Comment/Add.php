<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or
 * indirectly through a Keestash authorized reseller or distributor (a "Reseller"). Please read this EULA agreement
 * carefully before completing the installation process and using the Keestash software. It provides a license to use
 * the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Api\Comment;

use DateTime;
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\User\UserService;
use KSA\PasswordManager\Entity\Comment\Comment;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\HTTP\IJWTService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;
    private UserService       $userService;
    private ILogger           $logger;
    private IJWTService       $jwtService;

    public function __construct(
        CommentRepository $commentRepository
        , NodeRepository $nodeRepository
        , UserService $userService
        , ILogger $logger
        , IJWTService $jwtService
    ) {
        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->userService       = $userService;
        $this->logger            = $logger;
        $this->jwtService        = $jwtService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters    = (array) $request->getParsedBody();
        $commentString = $parameters['comment'] ?? null;
        $nodeId        = $parameters['node_id'] ?? null;

        if (null === $commentString) {
            throw new CommentException();
        }

        if (null === $nodeId) {
            throw new CommentException();
        }

        /** @var IToken $token */
        $token         = $request->getAttribute(IToken::class);
        $node          = $this->nodeRepository->getNode((int) $nodeId);
        $commentString = trim($commentString);

        if (true === $this->userService->isDisabled($token->getUser())) {
            throw new CommentException();
        }

        if ("" === $commentString) {
            throw new CommentException();
        }

        if (null === $node) {
            throw new CommentException();
        }

        if ($token->getUser()->getId() !== $node->getUser()->getId()) {
            throw new CommentException();
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

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "comment" => $comment
            ]
        );

    }

}
