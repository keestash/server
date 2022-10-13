<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use DateTimeImmutable;
use doganoo\DI\Object\String\IStringService;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\Node\Credential\CredentialException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\Service\L10N\IL10N;
use Psr\Log\LoggerInterface as ILogger;
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
        IL10N               $l10n
        , NodeRepository    $nodeRepository
        , IStringService    $stringService
        , CredentialService $credentialService
        , ILogger           $logger
    ) {
        $this->translator        = $l10n;
        $this->nodeRepository    = $nodeRepository;
        $this->stringService     = $stringService;
        $this->credentialService = $credentialService;
        $this->logger            = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = (array) $request->getParsedBody();
        $name         = $parameters["name"] ?? null;
        $username     = ($parameters["username"]["plain"]) ?? null;
        $url          = ($parameters["url"]["plain"]) ?? null;
        $nodeId       = $parameters["nodeId"] ?? null;
        $password     = $parameters["password"]['plain'] ?? null;
        $savePassword = null !== $password;

        $hasChanges = false;

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (false === ($node instanceof Credential)) {
            throw new CredentialException('node is not instance of credential');
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

        if (false === $this->stringService->isEmpty($password)) {
            $hasChanges = true;
        }

        // TODO abort when no changes?!
        $this->credentialService->updateCredential(
            $node
            , $username
            , $url
            , $name
        );

        if (true === $savePassword) {
            $this->credentialService->updatePassword($node, $password);
        }

        /** @var Node $parent */
        $parent = $this->nodeRepository->getParentNode(
            $node->getId()
        );

        $parent->setUpdateTs(new DateTimeImmutable());
        $this->nodeRepository->updateNode($parent);

        return new JsonResponse(
            [
                "message"           => $this->translator->translate("updated")
                , "has_changes"     => $hasChanges
                , "passwordChanged" => $savePassword
            ]
            , IResponse::OK
        );


    }

}
