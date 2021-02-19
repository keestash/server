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

namespace Keestash\Core\Service\Organization;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\Organization\Organization;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Service\Organization\IOrganizationService;

class OrganizationService implements IOrganizationService {

    private IDateTimeService $dateTimeService;

    public function __construct(IDateTimeService $dateTimeService) {
        $this->dateTimeService = $dateTimeService;
    }

    public function toOrganization(array $data): IOrganization {
        $organization = new Organization();
        $organization->setId((int) $data["id"]);
        $organization->setActiveTs(
            $this->dateTimeService->fromString($data["active_ts"]["date"])
        );
        $organization->setCreateTs(
            $this->dateTimeService->fromString($data["create_ts"]["date"])
        );
//        $organization->setMemberCount((int) $data['users']); // TODO to users!
        $organization->setName($data['name']);
        return $organization;
    }

}