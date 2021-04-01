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

use Keestash\Api\AbstractApi;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\HTTP\Input\SanitizerService;
use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node as NodeObject;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;

use KSP\L10N\IL10N;

/**
 * Class CredentialCreate
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Create extends AbstractApi {

    private NodeRepository        $nodeRepository;
    private EncryptionService     $encryptionService;
    private NodeService           $nodeService;
    private KeyService            $keyService;
    private CredentialService     $credentialService;
    private SanitizerService      $sanitizerService;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , EncryptionService $encryptionService
        , NodeService $nodeService
        , KeyService $keyService
        , CredentialService $credentialService
        , SanitizerService $sanitizerService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository       = $nodeRepository;
        $this->encryptionService    = $encryptionService;
        $this->nodeService          = $nodeService;
        $this->keyService           = $keyService;
        $this->credentialService    = $credentialService;
        $this->sanitizerService     = $sanitizerService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $title    = $this->getParameter("title", '');
        $userName = $this->getParameter("user_name", '');
        $password = $this->getParameter("password", '');
        $notes    = $this->getParameter("notes", '');
        $folder   = $this->getParameter("parent", '');
        $url      = $this->getParameter("url", '');

        if (false === $this->isValid($title)) {

            parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("No Title")
                ]
            );
            return;

        }

        $parent = $this->getParentNode($folder);

        if (
            // no parent found
            null === $parent
            // parent is not an folder
            || !$parent instanceof Folder
            // parent does not belong to me
            || $parent->getUser()->getId() !== $this->getToken()->getUser()->getId()
        ) {

            parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no parent found")
                ]
            );

            return;

        }

        $credential = $this->credentialService->createCredential(
            $password
            , $url
            , $userName
            , $title
            , $this->getToken()->getUser()
            , $parent
            , $this->sanitizerService->sanitize($notes)
        );

        $inserted = $this->credentialService->insertCredential($credential, $parent);

        if (false === $inserted) {
            parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not link edges")
                ]
            );
            return;
        }

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("success")
            ]
        );
    }

    private function isValid($value): bool {
        if (null === $value) return false;
        if ("" === trim($value)) return false;
        return true;
    }

    private function getParentNode($parent): ?NodeObject {

        if (Application::ROOT_FOLDER === $parent) {
            return $this->nodeRepository->getRootForUser($this->getToken()->getUser());
        }
        return $this->nodeRepository->getNode((int) $parent);
    }

    public function afterCreate(): void {
        // silence is golden
    }

}
