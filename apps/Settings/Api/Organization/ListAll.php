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
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Organization\IOrganization;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAll implements RequestHandlerInterface {

    private IOrganizationRepository $organizationRepository;

    public function __construct(IOrganizationRepository $organizationRepository) {
        $this->organizationRepository = $organizationRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $includeActive = $request->getAttribute('includeInactive', false);
        $organizations = $this->organizationRepository->getAll();

        if (false === $includeActive) {

            /**
             * @var IOrganization $organization
             */
            foreach ($organizations as $key => $organization) {
                if ($organization->getActiveTs() !== null) {
                    continue;
                }
                $organizations->remove($key);
            }
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "organizations" => $organizations->toArray()
            ]
        );
    }

}