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

namespace KSA\PasswordManager\Api\Node;

use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class GetByName
 * @package KSA\PasswordManager\Api\Node
 */
class GetByName implements RequestHandlerInterface {

    private NodeRepository $nodeRepository;
    private AccessService  $accessService;

    public function __construct(
        NodeRepository  $nodeRepository
        , AccessService $accessService
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->accessService  = $accessService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $name = $request->getAttribute('name');
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $name) {
            return new JsonResponse(
                [
                    "message" => "no username"
                ],
                IResponse::BAD_REQUEST
            );
        }

        $list = $this->nodeRepository->getByName($name, $token->getUser(), 0, 1);

        /** @var Node $node */
        foreach ($list as $key => $node) {
            if (false === $this->accessService->hasAccess($node, $token->getUser())) {
                $list->remove($key);
            }
        }

        return new JsonResponse(
            [
                "message" => $list
            ]
            , IResponse::OK
        );
    }

}