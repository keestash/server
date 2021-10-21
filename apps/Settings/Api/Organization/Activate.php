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

use DateTime;
use Keestash\Api\Response\LegacyResponse;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Activate implements RequestHandlerInterface {

    private IOrganizationRepository $organizationRepository;
    private ILogger                 $logger;

    public function __construct(
        IOrganizationRepository $organizationRepository
        , ILogger $logger
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->logger                 = $logger;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string)$request->getBody(), true);
        $id         = $parameters['id'] ?? null;
        $activate   = $parameters['activate'] ?? null;
        $activate   = $activate === 'true';

        if (null === $id || "" === $id || false === is_numeric($id)) {
            throw new GeneralApiException('no id provided');
        }

        $organization = $this->organizationRepository->get((int) $id);

        if (null === $organization) {
            throw new GeneralApiException('no organization found');
        }

        $organization->setActiveTs(
            true === $activate
                ? new DateTime()
                : null
        );

        $this->organizationRepository->update($organization);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "organization" => $organization
            ]
        );
    }


}