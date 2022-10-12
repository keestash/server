<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSP\Core\Repository\EncryptionKey\Organization;

use Doctrine\DBAL\Exception;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Repository\IRepository;

interface IOrganizationKeyRepository extends IRepository {

    /**
     * @param IOrganization $organization
     * @param IKey          $key
     * @return IKey
     * @throws Exception
     * @throws KeestashException
     */
    public function storeKey(IOrganization $organization, IKey $key): IKey;

    public function updateKey(IKey $key): bool;

    /**
     * @param IOrganization $organization
     * @return IKey
     * @throws KeestashException
     */
    public function getKey(IOrganization $organization): IKey;

    public function remove(IOrganization $organization): bool;

}
