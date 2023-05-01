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

use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Entity\Node\Node as NodeObject;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class CredentialCreate
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Create implements RequestHandlerInterface {

    public function __construct(
        private readonly NodeRepository          $nodeRepository
        , private readonly CredentialService     $credentialService
        , private readonly LoggerInterface       $logger
        , private readonly NodeEncryptionService $nodeEncryptionService
        , private readonly AccessService         $accessService
        , private readonly IActivityService      $activityService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = (array) $request->getParsedBody();
        $this->logger->debug('input', ['parsedBody' => $parameters, 'body' => $request->getBody()]);
        $name     = $parameters["name"] ?? '';
        $userName = $parameters["username"] ?? '';
        $password = ($parameters["password"]["value"]) ?? '';
        $folder   = $parameters["parent"] ?? '';
        $url      = $parameters["url"] ?? '';

        if (false === $this->isValid($name)) {
            $this->logger->info('invalid name given', ['name' => $name]);
            return new JsonResponse(
                ['invalid name'],
                IResponse::BAD_REQUEST
            );
        }

        try {
            $parent = $this->getParentNode((string) $folder, $token);
        } catch (PasswordManagerException $exception) {
            $this->logger->error('exception occured while parent request', ['exception' => $exception]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (
            // parent is not a folder
            !$parent instanceof Folder
            // parent does not belong to me
            || false === $this->accessService->hasAccess($parent, $token->getUser())
        ) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $credential = $this->credentialService->createCredential(
            (string) $password
            , $url
            , (string) $userName
            , $name
            , $token->getUser()
        );

        try {
            $edge = $this->credentialService->insertCredential($credential, $parent);
            $this->nodeEncryptionService->decryptNode($credential);
            $edge->setNode($credential);

            $this->activityService->insertActivityWithSingleMessage(
                ConfigProvider::APP_ID
                , (string) $credential->getId()
                , "created credential"
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['edge' => $edge], IResponse::OK);
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
            return $this->nodeRepository->getRootForUser($token->getUser(), 0, 1);
        }
        return $this->nodeRepository->getNode((int) $parent);
    }

}
