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

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\HTTP\Input\SanitizerService;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Node as NodeObject;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Class CredentialCreate
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Create implements RequestHandlerInterface {

    private IL10N             $translator;
    private NodeRepository    $nodeRepository;
    private CredentialService $credentialService;
    private SanitizerService  $sanitizerService;
    private ILogger           $logger;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , CredentialService $credentialService
        , SanitizerService $sanitizerService
        , ILogger $logger
    ) {
        $this->translator        = $l10n;
        $this->nodeRepository    = $nodeRepository;
        $this->credentialService = $credentialService;
        $this->sanitizerService  = $sanitizerService;
        $this->logger            = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = (array) $request->getParsedBody();
        $name       = $parameters["name"] ?? '';
        $userName   = $parameters["username"] ?? '';
        $password   = $parameters["password"] ?? '';
        $notes      = $parameters["note"] ?? '';
        $folder     = $parameters["parent"] ?? '';
        $url        = $parameters["url"] ?? '';

        if (false === $this->isValid($name)) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("No Title")
                ]
            );

        }

        try {
            $parent = $this->getParentNode($folder, $token);
        } catch (PasswordManagerException $exception) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no parent found")
                ]
            );
        }

        if (
            // parent is not a folder
            !$parent instanceof Folder
            // parent does not belong to me
            || $parent->getUser()->getId() !== $token->getUser()->getId()
        ) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no parent found")
                ]
            );

        }

        $credential = $this->credentialService->createCredential(
            $password
            , $url
            , $userName
            , $name
            , $token->getUser()
            , $this->sanitizerService->sanitize($notes)
        );

        try {
            $edge = $this->credentialService->insertCredential($credential, $parent);
            // tradeoff: we need to re-query as we want to get the decrypted data
            // normally, this should take place in a service or somewhere else.
            // should be refactored ASAP
            $edge->setNode(
                $this->nodeRepository->getNode($credential->getId())
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could not link edges")
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "edge" => $edge
            ]
        );
    }

    private function isValid($value): bool {
        if (null === $value) return false;
        if ("" === trim($value)) return false;
        return true;
    }

    /**
     * @param        $parent
     * @param IToken $token
     * @return NodeObject
     * @throws PasswordManagerException
     * @throws InvalidNodeTypeException
     */
    private function getParentNode($parent, IToken $token): NodeObject {

        if (NodeObject::ROOT === $parent) {
            return $this->nodeRepository->getRootForUser($token->getUser());
        }
        return $this->nodeRepository->getNode((int) $parent);
    }

}
