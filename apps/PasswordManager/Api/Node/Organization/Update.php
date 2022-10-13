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

use DateTime;
use Exception;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Event\NodeOrganizationUpdatedEvent;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository as OrganizationNodeRepository;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\Event\IEventService;
use Psr\Log\LoggerInterface as ILogger;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Update implements RequestHandlerInterface {

    private NodeRepository             $nodeRepository;
    private IOrganizationRepository    $organizationRepository;
    private ILogger                    $logger;
    private OrganizationNodeRepository $organizationNodeRepository;
    private IEventService              $eventManager;

    public function __construct(
        NodeRepository               $nodeRepository
        , IOrganizationRepository    $organizationRepository
        , ILogger                    $logger
        , OrganizationNodeRepository $organizationNodeRepository
        , IEventService              $eventManager
    ) {
        $this->nodeRepository             = $nodeRepository;
        $this->organizationRepository     = $organizationRepository;
        $this->logger                     = $logger;
        $this->organizationNodeRepository = $organizationNodeRepository;
        $this->eventManager               = $eventManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();

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
                'node does not belong to an organization. Pleas use the add endpoint'
                , IResponse::FORBIDDEN
            );
        }

        $organization = $this->organizationRepository->get($organizationId);

        if (null === $organization) {
            return new JsonResponse(
                'no organization found'
                , IResponse::NOT_FOUND
            );
        }

        if (null === $organization->getActiveTs() || $organization->getActiveTs() > (new DateTime())) {
            return new JsonResponse(
                'organization is not active'
                , IResponse::FORBIDDEN
            );
        }

        try {
            $this->organizationNodeRepository->replaceNodeOrganization(
                $node
                , $organization
            );
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage() . ': ' . $exception->getTraceAsString());
            return new JsonResponse(
                'could not add node to organization'
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        $this->eventManager->execute(
            new NodeOrganizationUpdatedEvent($node, $organization)
        );
        return new JsonResponse([
            'organization' => $organization
            , 'type'       => Edge::TYPE_ORGANIZATION
        ]);
    }

}