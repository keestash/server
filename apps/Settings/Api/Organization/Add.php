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
use doganoo\DI\Encryption\User\IUserService;
use Exception;
use Keestash\Core\DTO\Organization\Organization;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\Settings\Event\Organization\OrganizationAddedEvent;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    private IOrganizationRepository $organizationRepository;
    private IEventManager           $eventManager;
    private ILogger                 $logger;
    private IUserService            $userService;

    public function __construct(
        IOrganizationRepository $organizationRepository
        , IEventManager         $eventManager
        , ILogger               $logger
        , IUserService          $userService
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->eventManager           = $eventManager;
        $this->logger                 = $logger;
        $this->userService            = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);
        $name       = $parameters["organization"];

        if (null === $name || "" === $name) {
            throw new GeneralApiException('no organization found');
        }

        $organization = new Organization();
        $organization->setName($name);
        $organization->setPassword(
            $this->userService->hashPassword(
                bin2hex(random_bytes(16))
            )
        );
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(new DateTimeImmutable());

        try {
            $organization = $this->organizationRepository->insert($organization);
            $this->eventManager->execute(
                new OrganizationAddedEvent($organization)
            );
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage() . ' ' . $exception->getTraceAsString());
            return new JsonResponse(
                'error while adding organization'
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            [
                "organization" => $organization
            ]
        );
    }

}