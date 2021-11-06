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

namespace KSA\PasswordManager\Api\Node\Organization;

use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Event\NodeRemovedFromOrganizationEvent;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository as OrganizationNodeRepository;
use KSP\Api\IResponse;
use KSP\Core\Manager\EventManager\IEventManager;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    private NodeRepository             $nodeRepository;
    private OrganizationNodeRepository $organizationNodeRepository;
    private IEventManager              $eventManager;

    public function __construct(
        NodeRepository               $nodeRepository
        , OrganizationNodeRepository $organizationNodeRepository
        , IEventManager              $eventManager
    ) {
        $this->nodeRepository             = $nodeRepository;
        $this->organizationNodeRepository = $organizationNodeRepository;
        $this->eventManager               = $eventManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);

        $nodeId         = (int) ($parameters['node_id'] ?? 0);
        $organizationId = (int) ($parameters['organization_id'] ?? 0);

        if ($nodeId === 0 || $organizationId === 0) {
            return new JsonResponse(
                'node id or organization id not given'
                , IResponse::NOT_ACCEPTABLE
            );
        }

        $node = $this->nodeRepository->getNode($nodeId);

        if (null === $node->getOrganization()) {
            return new JsonResponse(
                'no organization set'
                , IResponse::NOT_FOUND
            );
        }

        if ($node->getOrganization()->getId() !== $organizationId) {
            return new JsonResponse(
                'organization does not match'
                , IResponse::NOT_ALLOWED
            );
        }

        $this->organizationNodeRepository->removeNodeOrganization($node);

        $this->eventManager->execute(
            new NodeRemovedFromOrganizationEvent($node, null)
        );
        return new JsonResponse(
            [
                'type' => Edge::TYPE_REGULAR
            ]
        );
    }

}