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

use Keestash\Api\Response\LegacyResponse;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Organization\IOrganizationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Update implements RequestHandlerInterface {

    private IOrganizationService    $organizationService;
    private IOrganizationRepository $organizationRepository;
    private ILogger                 $logger;

    public function __construct(
        IOrganizationService $organizationService
        , IOrganizationRepository $organizationRepository
        , ILogger $logger
    ) {
        $this->organizationService    = $organizationService;
        $this->logger                 = $logger;
        $this->organizationRepository = $organizationRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = json_decode((string)$request->getBody(), true);
        $organization = $parameters['organization'] ?? null;
        $organization = json_decode($organization, true);

        if (null === $organization) {
            throw new GeneralApiException();
        }

        $organization = $this->organizationService->toOrganization($organization);
        $organization = $this->organizationRepository->update($organization);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                'organization' => $organization
            ]
        );
    }

}