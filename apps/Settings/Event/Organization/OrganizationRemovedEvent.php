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

namespace KSA\Settings\Event\Organization;

use Keestash\Core\DTO\Event\Event;
use KSP\Core\DTO\Organization\IOrganization;

class OrganizationRemovedEvent extends Event {

    private IOrganization $organization;

    public function __construct(IOrganization $organization) {
        $this->organization = $organization;
    }

    public function getOrganization(): IOrganization {
        return $this->organization;
    }

    public function jsonSerialize(): array {
        return [
            'organization' => $this->getOrganization()
        ];
    }

}