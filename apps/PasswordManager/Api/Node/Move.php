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

namespace KSA\PasswordManager\Api\Node;

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Move
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Move implements RequestHandlerInterface {

    private NodeRepository $nodeRepository;
    private IL10N          $translator;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
    ) {
        $this->translator     = $l10n;
        $this->nodeRepository = $nodeRepository;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = (array) $request->getParsedBody();
        $nodeId       = $parameters["id"] ?? null;
        $targetNodeId = $parameters["target_node_id"] ?? null;
        $parentNodeId = $parameters["parent_node_id"] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("node does not exist")
                ],
                IResponse::NOT_FOUND
            );
        }

        if ($node->getUser()->getId() !== $token->getUser()->getId()) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("node does not exist")
                ]
            );
        }

        try {
            /** @var Folder $targetNode */
            $targetNode = $this->nodeRepository->getNode((int) $targetNodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("target does not exist")
                ]
                , IResponse::NOT_FOUND
            );
        }

        if (
            $targetNode->isSharedTo($token->getUser())
            || $targetNode->getUser()->getId() !== $token->getUser()->getId()
        ) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("target does not exist")
                ]
            );
        }

        try {
            /** @var Folder $parentNode */
            $parentNode = $this->nodeRepository->getNode((int) $parentNodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("parent does not exist")
                ]
                , IResponse::NOT_FOUND
            );

        }

        if (
            $targetNode->isSharedTo($token->getUser())
            || $targetNode->getUser()->getId() !== $token->getUser()->getId()
        ) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("parent does not exist")
                ]
            );
        }

        $moved = $this->nodeRepository->move(
            $node
            , $parentNode
            , $targetNode
        );

        if (false === $moved) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not move node"
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => "moved node"
            ]
        );
    }

}
