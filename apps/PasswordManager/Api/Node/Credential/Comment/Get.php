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

namespace KSA\PasswordManager\Api\Node\Credential\Comment;

use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;

    public function __construct(
        CommentRepository $commentRepository
        , NodeRepository  $nodeRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $nodeId    = $request->getAttribute("nodeId");
        $sortField = $request->getAttribute("sortField");
        $sortDir   = $request->getAttribute("sortDirection");

        if (null === $nodeId) {
            return new JsonResponse('no node found', IResponse::BAD_REQUEST);
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse('no node found', IResponse::NOT_FOUND);
        }

        $comments = $this->commentRepository->getCommentsByNode(
            $node
            , $sortField
            , $sortDir
        );

        return new JsonResponse(
            [
                "comments" => $comments
            ]
            , IResponse::OK
        );
    }

}
