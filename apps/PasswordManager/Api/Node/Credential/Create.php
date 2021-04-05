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
use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node as NodeObject;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , CredentialService $credentialService
        , SanitizerService $sanitizerService
    ) {
        $this->translator        = $l10n;
        $this->nodeRepository    = $nodeRepository;
        $this->credentialService = $credentialService;
        $this->sanitizerService  = $sanitizerService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = json_decode((string) $request->getBody(), true);
        $title      = $parameters["title"] ?? '';
        $userName   = $parameters["user_name"] ?? '';
        $password   = $parameters["password"] ?? '';
        $notes      = $parameters["notes"] ?? '';
        $folder     = $parameters["parent"] ?? '';
        $url        = $parameters["url"] ?? '';

        if (false === $this->isValid($title)) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("No Title")
                ]
            );

        }

        $parent = $this->getParentNode($folder, $token);

        if (
            // no parent found
            null === $parent
            // parent is not an folder
            || !$parent instanceof Folder
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
            , $title
            , $token->getUser()
            , $parent
            , $this->sanitizerService->sanitize($notes)
        );

        $inserted = $this->credentialService->insertCredential($credential, $parent);

        if (false === $inserted) {
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
                "message" => $this->translator->translate("success")
            ]
        );
    }

    private function isValid($value): bool {
        if (null === $value) return false;
        if ("" === trim($value)) return false;
        return true;
    }

    private function getParentNode($parent, IToken $token): ?NodeObject {

        if (Application::ROOT_FOLDER === $parent) {
            return $this->nodeRepository->getRootForUser($token->getUser());
        }
        return $this->nodeRepository->getNode((int) $parent);
    }

}
