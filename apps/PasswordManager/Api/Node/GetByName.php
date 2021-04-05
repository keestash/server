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

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
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

    public function __construct(NodeRepository $nodeRepository) {
        $this->nodeRepository = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $name = $request->getAttribute('name');
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $name) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no username"
                ]
            );
        }

        $list = $this->nodeRepository->getByName($name, 0, 1);

        /** @var Node $node */
        foreach ($list as $key => $node) {
            if ($node->getUser()->getId() !== $token->getUser()->getId()) {
                $list->remove($key);
            }
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $list
            ]
        );
    }

}