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
use Keestash\Api\AbstractApi;

use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Service\User\UserService;
use KSA\PasswordManager\Entity\Comment\Comment;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class Add extends AbstractApi {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;
    private UserRepository    $userRepository;
    private UserService       $userService;

    public function __construct(
        IL10N $l10n
        , CommentRepository $commentRepository
        , NodeRepository $nodeRepository
        , UserRepository $userRepository
        , UserService $userService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->userRepository    = $userRepository;
        $this->userService       = $userService;
    }

    public function onCreate(array $parameters): void {

    }

    /**
     * @throws CommentException
     */
    public function create(): void {
        $userId        = $this->getParameter('user_id');
        $commentString = $this->getParameter('comment');
        $nodeId        = $this->getParameter('node_id');

        if (null === $userId) {
            throw new CommentException();
        }

        if (null === $commentString) {
            throw new CommentException();
        }

        if (null === $nodeId) {
            throw new CommentException();
        }

        $commentString = trim($commentString);
        $user          = $this->userRepository->getUserById((string) $userId);
        $node          = $this->nodeRepository->getNode((int) $nodeId);

        if (true === $this->userService->isDisabled($user)) {
            throw new CommentException();
        }

        if ("" === $commentString) {
            throw new CommentException();
        }

        if (null === $node) {
            throw new CommentException();
        }

        if ($user->getId() !== $node->getUser()->getId()) {
            throw new CommentException();
        }

        $comment = new Comment();
        $comment->setComment($commentString);
        $comment->setCreateTs(new DateTime());
        $comment->setNode($node);
        $comment->setUser($user);
        $comment = $this->commentRepository->addComment($comment);

        if (null === $comment) {
            throw new CommentException();
        }

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "comment" => $comment
            ]
        );

    }

    public function afterCreate(): void {

    }

}
