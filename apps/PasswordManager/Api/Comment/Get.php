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

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;

    public function __construct(
        CommentRepository $commentRepository
        , NodeRepository $nodeRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $nodeId = $request->getAttribute("nodeId");
        $token  = $request->getAttribute(IToken::class);

        if (null === $nodeId) {
            throw new CommentException();
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            throw new CommentException();
        }

        if (false === $this->hasAccess($token->getUser(), $node)) {
            throw new CommentException();
        }

        $comments = $this->commentRepository->getCommentsByNode($node);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "comments" => $comments
            ]
        );
    }

    private function hasAccess(IUser $user, Node $node): bool {
        if ($node->getUser()->getId() === $user->getId()) return true;
        if (true === $node->isSharedTo($user)) return true;
        return false;
    }

}
