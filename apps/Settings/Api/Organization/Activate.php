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
use Exception;
use KSA\Settings\Event\Organization\OrganizationActivatedEvent;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Logger\ILogger;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Activate implements RequestHandlerInterface {

    private IOrganizationRepository $organizationRepository;
    private ILogger       $logger;
    private IEventService $eventManager;

    public function __construct(
        IOrganizationRepository $organizationRepository
        , ILogger               $logger
        , IEventService         $eventManager
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->logger                 = $logger;
        $this->eventManager           = $eventManager;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $id         = (int) ($parameters['id'] ?? 0);
        $activate   = $parameters['activate'] ?? null;

        if ($id < 1) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $organization = $this->organizationRepository->get($id);

        if (null === $organization) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $organization->setActiveTs(
            true === $activate
                ? new DateTimeImmutable()
                : null
        );

        try {
            $organization = $this->organizationRepository->update($organization);
            $this->eventManager->execute(
                new OrganizationActivatedEvent($organization)
            );
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage() . ' ' . $exception->getTraceAsString());
            return new JsonResponse(
                'error while updating organization'
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            [
                "organization" => $organization
            ]
            , IResponse::OK
        );
    }

}