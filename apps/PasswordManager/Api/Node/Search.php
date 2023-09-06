<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Search implements RequestHandlerInterface {

    public function __construct(
        private readonly NodeRepository          $nodeRepository
        , private readonly LoggerInterface       $logger
        , private readonly NodeEncryptionService $nodeEncryptionService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token  = $request->getAttribute(IToken::class);
        $search = (string) $request->getAttribute('search');
        $nodes  = $this->nodeRepository->search($search, $token->getUser());

        $newList = new ArrayList();
        foreach ($nodes as $node) {
            $this->nodeEncryptionService->decryptNode($node);
            $newList->add($node);
        }
        $this->logger->error('nodes', ['nodes' => $nodes->toArray()]);
        return new JsonResponse(
            [
                'result' => $newList->toArray()
            ],
            IResponse::OK
        );
    }

}