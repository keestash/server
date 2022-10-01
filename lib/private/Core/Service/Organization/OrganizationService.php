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
use Exception;
use Keestash\Core\DTO\Organization\Organization;
use Keestash\Exception\IndexNotFoundException;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Service\Organization\IOrganizationService;
use TypeError;

class OrganizationService implements IOrganizationService {

    private IDateTimeService $dateTimeService;

    public function __construct(IDateTimeService $dateTimeService) {
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * @param array $data
     * @return IOrganization
     * @throws IndexNotFoundException
     */
    public function toOrganization(array $data): IOrganization {
        try {
            $organization = new Organization();
            $organization->setId((int) $data["id"]);
            $organization->setActiveTs($data["active_ts"]);
            $organization->setCreateTs($data["create_ts"]);
//        $organization->setMemberCount((int) $data['users']); // TODO to users!
            $organization->setName($data['name']);
            return $organization;
        } catch (Exception|TypeError $exception) {
            throw new IndexNotFoundException($exception->getMessage());
        }
    }

}