<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\NodeEncryptionService;
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

    private IL10N                 $translator;
    private NodeRepository        $nodeRepository;
    private CredentialService     $credentialService;
    private SanitizerService      $sanitizerService;
    private ILogger               $logger;
    private NodeEncryptionService $nodeEncryptionService;
    private AccessService         $accessService;

    public function __construct(
        IL10N                   $l10n
        , NodeRepository        $nodeRepository
        , CredentialService     $credentialService
        , SanitizerService      $sanitizerService
        , ILogger               $logger
        , NodeEncryptionService $nodeEncryptionService
        , AccessService         $accessService
    ) {
        $this->translator            = $l10n;
        $this->nodeRepository        = $nodeRepository;
        $this->credentialService     = $credentialService;
        $this->sanitizerService      = $sanitizerService;
        $this->logger                = $logger;
        $this->nodeEncryptionService = $nodeEncryptionService;
        $this->accessService         = $accessService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = (array) $request->getParsedBody();
        $name       = $parameters["name"] ?? '';
        $userName   = $parameters["username"] ?? '';
        $password   = $parameters["password"] ?? '';
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
            || false === $this->accessService->hasAccess($parent, $token->getUser())
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
        );

        try {
            $edge = $this->credentialService->insertCredential($credential, $parent);
            $this->nodeEncryptionService->decryptNode($credential);
            $edge->setNode($credential);
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

    private function isValid(?string $value): bool {
        if (null === $value) return false;
        if ("" === trim($value)) return false;
        return true;
    }

    /**
     * @param string $parent
     * @param IToken $token
     * @return NodeObject
     * @throws PasswordManagerException
     * @throws InvalidNodeTypeException
     */
    private function getParentNode(string $parent, IToken $token): NodeObject {

        if (NodeObject::ROOT === $parent) {
            return $this->nodeRepository->getRootForUser($token->getUser());
        }
        return $this->nodeRepository->getNode((int) $parent);
    }

}
