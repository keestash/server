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

namespace KSA\Settings\Api\Organization;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\OrganizationNotUpdatedException;
use KSA\Settings\Event\Organization\OrganizationUpdatedEvent;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Service\Organization\IOrganizationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Update implements RequestHandlerInterface {

    private IOrganizationService    $organizationService;
    private IOrganizationRepository $organizationRepository;
    private ILogger                 $logger;
    private IEventManager           $eventManager;

    public function __construct(
        IOrganizationService      $organizationService
        , IOrganizationRepository $organizationRepository
        , ILogger                 $logger
        , IEventManager           $eventManager
    ) {
        $this->organizationService    = $organizationService;
        $this->logger                 = $logger;
        $this->organizationRepository = $organizationRepository;
        $this->eventManager           = $eventManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $id         = $parameters['id'] ?? -1;
        $active     = true === ($parameters['active'] ?? false);
        $name       = (string) ($parameters['name'] ?? '');

        $organization = $this->organizationRepository->get((int) $id);

        if (null === $organization) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }
        if ("" === $name) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $organization->setActiveTs(
            true === $active
                ? (new DateTimeImmutable())
                : null
        );
        $organization->setName($name);

        try {
            $organization = $this->organizationRepository->update($organization);
            $this->eventManager->execute(
                new OrganizationUpdatedEvent($organization)
            );
        } catch (OrganizationNotUpdatedException $exception) {
            $this->logger->error('error while converting to organization', ['exception' => $exception]);
            return new JsonResponse(
                ['error while updating organization']
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            [
                'organization' => $organization
            ]
            , IResponse::OK
        );
    }

}