<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Credential\Password;

use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Core\DTO\Token\IToken;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Update
 * @package KSA\PasswordManager\Api\Node\Credential\Password
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Update implements RequestHandlerInterface {

    public function __construct(
        private readonly NodeRepository          $nodeRepository
        , private readonly CredentialService     $credentialService
        , private readonly NodeEncryptionService $nodeEncryptionService
        , private readonly IActivityService      $activityService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters    = (array) $request->getParsedBody();
        $passwordPlain = $parameters['passwordPlain'] ?? null;
        $nodeId        = $parameters['nodeId'] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $nodeId || null === $passwordPlain) {
            throw new PasswordManagerException('passwordPlain or nodeId is empty');
        }

        $credential = $this->nodeRepository->getNode((int) $nodeId, 0, 1);
        $this->nodeEncryptionService->decryptNode($credential);

        if (!($credential instanceof Credential)) {
            throw new PasswordManagerException('node is not a credential');
        }

        $this->credentialService->updatePassword(
            $credential
            , $passwordPlain
        );

        $this->activityService->insertActivityWithSingleMessage(
            ConfigProvider::APP_ID
            , (string) $credential->getId()
            , sprintf(
                "updated credential by %s",
                $token->getUser()->getName()
            )
        );

        return new JsonResponse([]);
    }

}