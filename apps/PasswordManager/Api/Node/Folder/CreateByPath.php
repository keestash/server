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

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\HTTP\IResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class NodeCreate
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class CreateByPath implements RequestHandlerInterface {

    public const VALID_DELIMITERS = ['/'];

    public function __construct(
        private readonly NodeService        $nodeService
        , private readonly IResponseService $responseService
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $this->logger->debug('start create by path');
        /** @var IToken $token */
        $token        = $request->getAttribute(IToken::class);
        $parameters   = (array) $request->getParsedBody();
        $path         = $parameters['path'] ?? null;
        $delimiter    = $parameters['delimiter'] ?? null;
        $parentNodeId = $parameters['parentNodeId'] ?? Node::ROOT;
        $forceCreate  = true === $parameters['forceCreate'];
        $user         = $token->getUser();
        $responses    = new HashTable();

        $this->logger->debug('parameters', $parameters);
        if (false === in_array($delimiter, CreateByPath::VALID_DELIMITERS, true)) {
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_INVALID_FOLDER_DELIMITER)
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $folderNames = explode($delimiter, $path);
        $folderCount = count($folderNames);

        $parent = $this->nodeService->getFolder($parentNodeId, $user, 0, $folderCount);

        $this->logger->debug('foldernames', ['folderNames' => $folderNames, 'parent' => ['id' => $parent->getId(), 'name' => $parent->getName()]]);
        foreach ($folderNames as $folderName) {
            if (false === $this->nodeService->validFolderName($folderName)) {
                continue;
            }
            $this->logger->debug('going to create folder', ['folder' => $folderName, 'parent' => ['id' => $parent->getId(), 'name' => $parent->getName()]]);
            $child = $this->getChild($parent, $folderName);
            if (null === $child || true === $forceCreate) {
                $this->logger->debug('folder not found in parent, going to create', ['folderName' => $folderName, 'forceCreate' => $forceCreate]);
                $child = $this->nodeService->createFolder(
                    $folderName
                    , $user
                    , new DateTimeImmutable()
                    , $parent
                )->getNode();
            }
            $parent = $this->nodeService->getFolder($child->getId(), $user, 0, $folderCount);
            $responses->put(
                $folderName
                , [
                    'id'     => $child->getId()
                    , 'name' => $child->getName()
                ]
            );
        }

        $this->logger->debug('end create by path');
        return new JsonResponse(
            [
                "edge" => $responses->toArray()
            ]
            , IResponse::OK
        );
    }

    private function getChild(Folder $folder, string $name): ?Folder {
        /** @var Edge $edge */
        foreach ($folder->getEdges() as $edge) {
            if (
                $edge->getNode() instanceof Folder
                && $edge->getNode()->getName() === $name
            ) {
                return $edge->getNode();
            }
        }
        return null;
    }


}
