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

use Keestash\Api\AbstractApi;

use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

/**
 * Class Move
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Move extends AbstractApi {

    private NodeRepository $nodeRepository;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->nodeRepository = $nodeRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $nodeId       = $this->getParameter("id", null);
        $targetNodeId = $this->getParameter("target_node_id", null);
        $parentNodeId = $this->getParameter("parent_node_id", null);

        $node = $this->nodeRepository->getNode((int) $nodeId);
        /** @var Folder|null $targetNode */
        $targetNode = $this->nodeRepository->getNode((int) $targetNodeId);
        /** @var Folder|null $parentNode */
        $parentNode = $this->nodeRepository->getNode((int) $parentNodeId);

        if (null === $node || $node->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("node does not exist")
                ]
            );
            return;
        }

        if (
            null === $targetNode
            || $targetNode->isSharedTo($this->getToken()->getUser())
            || $targetNode->getUser()->getId() !== $this->getToken()->getUser()->getId()
        ) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("target does not exist")
                ]
            );
            return;
        }

        if (
            null === $parentNode
            || $targetNode->isSharedTo($this->getToken()->getUser())
            || $targetNode->getUser()->getId() !== $this->getToken()->getUser()->getId()
        ) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("parent does not exist")
                ]
            );
            return;
        }

        $moved = $this->nodeRepository->move(
            $node
            , $parentNode
            , $targetNode
        );

        if (false === $moved) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not move node"
                ]
            );
            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => "moved node"
            ]
        );
    }

    public function afterCreate(): void {

    }

}
