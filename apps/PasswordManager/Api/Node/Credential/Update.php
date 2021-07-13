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
use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Update
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * TODO
 *      handle non existent parameters
 *      handle more fields
 */
class Update implements RequestHandlerInterface {

    private NodeRepository    $nodeRepository;
    private IStringService    $stringService;
    private CredentialService $credentialService;
    private IL10N             $translator;
    private ILogger           $logger;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , IStringService $stringService
        , CredentialService $credentialService
        , ILogger $logger
    ) {
        $this->translator        = $l10n;
        $this->nodeRepository    = $nodeRepository;
        $this->stringService     = $stringService;
        $this->credentialService = $credentialService;
        $this->logger            = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = (array) $request->getParsedBody();
        $name       = $parameters["name"] ?? null;
        $username   = $parameters["username"] ?? null;
        $url        = $parameters["url"] ?? null;
        $nodeId     = $parameters["nodeId"] ?? null;

        $hasChanges = false;

        /** @var Credential|null $node */
        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (null === $node || $node->getUser()->getId() !== $token->getUser()->getId()) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no node found")
                ]
            );
        }

        if (false === $this->stringService->isEmpty($username)) {
            $hasChanges = true;
        }

        if (false === $this->stringService->isEmpty($url)) {
            $hasChanges = true;
        }

        if (false === $this->stringService->isEmpty($name)) {
            $hasChanges = true;
        }

        // TODO abort when no changes?!
        $this->credentialService->updateCredential(
            $node
            , $username
            , $url
            , $name
        );

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message"       => $this->translator->translate("updated")
                , "has_changes" => $hasChanges
            ]
        );


    }

}
