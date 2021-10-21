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
use Keestash\Core\DTO\Organization\Organization;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    private IOrganizationRepository $organizationRepository;

    public function __construct(IOrganizationRepository $organizationRepository) {
        $this->organizationRepository = $organizationRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        $parameters = json_decode((string) $request->getBody(), true);
        $name       = $parameters["organization"];

        if (null === $name || "" === $name) {
            throw new GeneralApiException('no organization found');
        }
        $organization = new Organization();
        $organization->setName($name);
        $organization->setCreateTs(new DateTime());
        $organization->setActiveTs(new DateTime());
        $organization = $this->organizationRepository->insert($organization);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "organization" => $organization
            ]
        );
    }


}