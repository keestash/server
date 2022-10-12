<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KST\Unit\Core\Service\Organization;

use DateTimeImmutable;
use Keestash\Exception\IndexNotFoundException;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Service\Organization\IOrganizationService;
use KST\TestCase;

class OrganizationServiceTest extends TestCase {

    private IOrganizationService $organizationService;

    protected function setUp(): void {
        parent::setUp();
        $this->organizationService = $this->getService(IOrganizationService::class);
    }

    public function testToOrganization(): void {
        $id           = 1;
        $activeTs     = new DateTimeImmutable();
        $createTs     = new DateTimeImmutable();
        $name         = OrganizationServiceTest::class;
        $organization = $this->organizationService->toOrganization(
            [
                'id'          => $id
                , 'active_ts' => $activeTs
                , 'create_ts' => $createTs
                , 'name'      => $name
            ]
        );

        $this->assertInstanceOf(IOrganization::class, $organization);
        $this->assertTrue($organization->getId() === $id);
        $this->assertTrue($organization->getActiveTs()->getTimestamp() === $activeTs->getTimestamp());
        $this->assertTrue($organization->getCreateTs()->getTimestamp() === $createTs->getTimestamp());
        $this->assertTrue($organization->getName() === $name);
    }

    public function testToOrganizationWithIndexNotFoundException(): void {
        $this->expectException(IndexNotFoundException::class);
        $id           = 1;
        $activeTs     = new DateTimeImmutable();
        $createTs     = new DateTimeImmutable();
        $name         = OrganizationServiceTest::class;
        $organization = $this->organizationService->toOrganization(
            [
                'id'          => $id
                , 'active_tz' => $activeTs
                , 'create_ts' => $createTs
                , 'name'      => $name
            ]
        );

        $this->assertInstanceOf(IOrganization::class, $organization);
        $this->assertTrue($organization->getId() === $id);
        $this->assertTrue($organization->getActiveTs()->getTimestamp() === $activeTs->getTimestamp());
        $this->assertTrue($organization->getCreateTs()->getTimestamp() === $createTs->getTimestamp());
        $this->assertTrue($organization->getName() === $name);
    }

}