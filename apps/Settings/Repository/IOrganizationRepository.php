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

namespace KSA\Settings\Repository;

use Doctrine\DBAL\Exception;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Exception\OrganizationNotUpdatedException;
use KSA\Settings\Exception\OrganizationNotFoundException;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;

/**
 * Interface IOrganizationService
 * @package KSA\Settings\Repository
 * @author  Dogan Ucar <dogan.ucar@check24.de>
 */
interface IOrganizationRepository {

    /**
     * @param IOrganization $organization
     * @return IOrganization
     */
    public function insert(IOrganization $organization): IOrganization;

    /**
     * @param IOrganization $organization
     * @return IOrganization
     * @throws OrganizationNotUpdatedException
     */
    public function update(IOrganization $organization): IOrganization;

    /**
     * @return ArrayList<IOrganization>
     */
    public function getAll(): ArrayList;

    /**
     * @return ArrayList<IOrganization>
     */
    public function getAllForUser(IUser $user): ArrayList;

    /**
     * @param int $id
     * @return IOrganization|null
     */
    public function get(int $id): ?IOrganization;

    /**
     * @param string $name
     * @return IOrganization
     * @throws Exception
     * @throws OrganizationNotFoundException
     */
    public function getByName(string $name): IOrganization;

    public function remove(IOrganization $organization): IOrganization;

}