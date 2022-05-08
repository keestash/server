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

use doganoo\DI\Object\String\IStringService;
use Keestash\Api\Response\JsonResponse;
use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Exception\Node\Credential\CredentialException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
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
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = (array) $request->getParsedBody();
        $name       = $parameters["name"] ?? null;
        $username   = $parameters["username"] ?? null;
        $url        = $parameters["url"] ?? null;
        $nodeId     = $parameters["nodeId"] ?? null;

        $hasChanges = false;

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (false === ($node instanceof Credential)) {
            throw new CredentialException();
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

        return new JsonResponse(
            [
                "message"       => $this->translator->translate("updated")
                , "has_changes" => $hasChanges
            ]
            , IResponse::OK
        );


    }

}
