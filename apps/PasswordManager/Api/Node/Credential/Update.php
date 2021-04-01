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

namespace KSA\PasswordManager\Api\Node\Credential;

use doganoo\DI\Object\String\IStringService;
use Keestash\Api\AbstractApi;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

/**
 * Class Update
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Update extends AbstractApi {

    private NodeRepository    $nodeRepository;
    private IStringService    $stringService;
    private CredentialService $credentialService;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , IStringService $stringService
        , CredentialService $credentialService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository    = $nodeRepository;
        $this->stringService     = $stringService;
        $this->credentialService = $credentialService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $name     = $this->getParameter("username");
        $url      = $this->getParameter("url");
        $nodeId   = $this->getParameter("nodeId");
        $password = $this->getParameter("password");

        $hasChanges = false;

        /** @var Credential $node */
        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (null === $node || $node->getUser()->getId() !== $this->getToken()->getId()) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no node found")
                ]
            );
            return;
        }

        if (false === $this->stringService->isEmpty($name)) {
            $hasChanges = true;
        }

        if (false === $this->stringService->isEmpty($url)) {
            $hasChanges = true;
        }

        if (false === $this->stringService->isEmpty($password)) {
            $hasChanges = true;
        }

        // TODO abort when no changes?!
        $this->credentialService->updateCredential(
            $node
            , $name
            , $url
            , $password
        );

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_NOT_OK
            , [
                "message"       => $this->getL10N()->translate("updated")
                , "has_changes" => $hasChanges
            ]
        );


    }

    public function afterCreate(): void {

    }

}
