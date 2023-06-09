<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Settings\Service;

use KSA\Settings\Event\Organization\OrganizationAddedEvent;
use KSA\Settings\Event\Organization\OrganizationRemovedEvent;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Service\Event\IEventService;

class OrganizationService implements IOrganizationService {

    public function __construct(
        private readonly IOrganizationRepository $organizationRepository
        , private readonly IEventService         $eventService
    ) {
    }

    public function add(IOrganization $organization): IOrganization {
        $organization = $this->organizationRepository->insert($organization);
        $this->eventService->execute(
            new OrganizationAddedEvent($organization)
        );
        return $organization;
    }

    public function remove(IOrganization $organization): IOrganization {
        $organization = $this->organizationRepository->remove($organization);
        $this->eventService->execute(
            new OrganizationRemovedEvent($organization)
        );
        return $organization;
    }

}