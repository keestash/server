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
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\ILogger\ILogger;

class OrganizationRepository {

    private IBackend $backend;
    private ILogger  $logger;

    public function __construct(
        IBackend  $backend
        , ILogger $logger
    ) {
        $this->backend = $backend;
        $this->logger  = $logger;
    }

    /**
     * @param Node          $node
     * @param IOrganization $organization
     * @throws \Doctrine\DBAL\Exception
     */
    public function addNodeToOrganization(Node $node, IOrganization $organization): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder
            ->insert('`organization_node`')
            ->values(
                [
                    'organization_id' => '?'
                    , 'node_id'       => '?'
                ]
            )
            ->setParameter(0, $organization->getId())
            ->setParameter(1, $node->getId());

        $queryBuilder->execute();

    }

    public function updateNodeRepository(Node $node, IOrganization $organization): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder
            ->update('`organization_node`')
            ->set('organization_id', '?')
            ->where('node_id = ?')
            ->setParameter(0, $organization->getId())
            ->setParameter(1, $node->getId());

        $queryBuilder->execute();
    }

    public function removeNodeRepository(Node $node): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        if (null === $node->getOrganization()) {
            throw new PasswordManagerException('missing organization');
        }

        $queryBuilder = $queryBuilder
            ->delete('`organization_node`')
            ->where('node_id = ?')
            ->andWhere('organization_id = ?')
            ->setParameter(0, $node->getId())
            ->setParameter(1, $node->getOrganization()->getId());

        $queryBuilder->execute();
    }

}