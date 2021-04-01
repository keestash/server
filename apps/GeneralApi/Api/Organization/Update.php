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

namespace KSA\GeneralApi\Api\Organization;

use Keestash\Api\AbstractApi;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\GeneralApi\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Organization\IOrganizationService;
use KSP\L10N\IL10N;

class Update extends AbstractApi {

    private IOrganizationService    $organizationService;
    private IOrganizationRepository $organizationRepository;
    private ILogger                 $logger;

    public function __construct(
        IOrganizationService $organizationService
        , IOrganizationRepository $organizationRepository
        , IL10N $l10n
        , ILogger $logger
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->organizationService    = $organizationService;
        $this->logger                 = $logger;
        $this->organizationRepository = $organizationRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $organization = $this->getParameter('organization');
        $organization = json_decode($organization, true);

        if (null === $organization) {
            throw new GeneralApiException();
        }

        $organization = $this->organizationService->toOrganization($organization);
        $organization = $this->organizationRepository->update($organization);

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                'organization' => $organization
            ]
        );
    }

    public function afterCreate(): void {

    }

}