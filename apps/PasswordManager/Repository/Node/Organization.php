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

namespace KSA\PasswordManager\Repository\Node;

use KSA\PasswordManager\Entity\Node;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Organization\IOrganization;

class Organization {

    private IBackend $backend;

    public function __construct(IBackend $backend) {
        $this->backend = $backend;
    }

    public function addNodeToOrganization(Node $node, IOrganization $organization): void {
        $this->backend->getConnection()->createQueryBuilder()
            ->insert('`organization_node`')
            ->values(
                [
                    'organization_id' => '?'
                    , 'node_id'       => '?'
                ]
            )
            ->setParameter(0, $organization->getId())
            ->setParameter(1, $node->getId())
            ->execute();
    }

}