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

use Keestash\Api\AbstractApi;
use KSA\PasswordManager\Exception\Node\Comment\CommentException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class Get extends AbstractApi {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;

    public function __construct(
        IL10N $l10n
        , CommentRepository $commentRepository
        , NodeRepository $nodeRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
    }

    public function onCreate(array $parameters): void {
     
    }

    public function create(): void {
        $nodeId = $this->getParameter("nodeId");

        if (null === $nodeId) {
            throw new CommentException();
        }

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (null === $node) {
            throw new CommentException();
        }

        if ($this->getToken()->getUser()->getId() !== $node->getUser()->getId()) {
            throw new CommentException();
        }

        $comments = $this->commentRepository->getCommentsByNode($node);

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "comments" => $comments
            ]
        );
    }

    public function afterCreate(): void {

    }

}
