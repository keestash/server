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

namespace KSA\PasswordManager\Api\Node\Folder;

use DateTime;
use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class NodeCreate
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Create implements RequestHandlerInterface {

    public function __construct(
        private readonly NodeRepository     $nodeRepository
        , private readonly NodeService      $nodeService
        , private readonly IActivityService $activityService
    ) {
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token      = $request->getAttribute(IToken::class);
        $parameters = (array) $request->getParsedBody();
        $name       = $parameters["name"] ?? null;
        $parent     = $parameters['node_id'] ?? null;
        if (null !== $parent) {
            $parent = (string) $parent;
        }

        if (false === $this->isValid($name) || false === $this->isValid($parent)) {
            return new JsonResponse(['invalid name or parent'], IResponse::BAD_REQUEST);
        }

        try {
            $parentNode = $this->getParentNode($parent, $token);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $folder = new Folder();
        $folder->setUser($token->getUser());
        $folder->setName((string) $name);
        $folder->setType(Node::FOLDER);
        $folder->setCreateTs(new DateTime());

        $lastId = $this->nodeRepository->addFolder($folder);

        if (null === $lastId || 0 === $lastId) {
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }

        $folder->setId($lastId);

        $edge = $this->nodeService->prepareRegularEdge(
            $folder
            , $parentNode
            , $token->getUser()
        );

        $edge = $this->nodeRepository->addEdge($edge);

        $this->activityService->insertActivityWithSingleMessage(
            ConfigProvider::APP_ID
            , (string) $folder->getId()
            , "updated node"
        );

        return new JsonResponse(
            [
                "edge" => $edge
            ]
            , IResponse::OK
        );
    }

    private function isValid(?string $value): bool {
        if (null === $value) return false;
        if ("" === $value) return false;
        return true;
    }

    private function getParentNode(string $parent, IToken $token): Folder {

        if (Node::ROOT === $parent) {
            return $this->nodeRepository->getRootForUser($token->getUser());
        }

        $node = $this->nodeRepository->getNode((int) $parent);

        if ($node instanceof Folder) {
            return $node;
        }

        throw new PasswordManagerException();
    }

}
