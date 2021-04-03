<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Service\Encryption\Credential;

use DateTime;
use Keestash\Core\DTO\Encryption\Credential\Credential;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Encryption\Credential\ICredentialService;

/**
 * Class CredentialService
 *
 * @package Keestash\Core\Service\Encryption\Credential
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class CredentialService implements ICredentialService {

    /**
     * Returns an instance of credential for the given user
     *
     * @param IUser $user
     *
     * @return ICredential
     */
    private function getCredentialForUser(IUser $user): ICredential {
        $credential = new Credential();
        $credential->setKeyHolder($user);
        $credential->setSecret($user->getPassword());
        $credential->setCreateTs(new DateTime());
        $credential->setId($user->getId());
        return $credential;
    }

    public function getCredential(IKeyHolder $keyHolder): ICredential {
        if ($keyHolder instanceof IUser) {
            return $this->getCredentialForUser($keyHolder);
        } else if ($keyHolder instanceof IOrganization) {
            return $this->getCredentialForOrganization($keyHolder);
        }
        throw new KeestashException();
    }

    /**
     * Returns an instance of credential for the given organization
     *
     * @param IOrganization $organization
     *
     * @return ICredential
     * @throws KeestashException
     */
    private function getCredentialForOrganization(IOrganization $organization): ICredential {

        if (0 === $organization->getUsers()->length()) {
            throw new KeestashException();
        }

        $credential = new Credential();
        $secret     = "";
        /** @var IUser $user */
        foreach ($organization->getUsers() as $user) {
            $secret = $secret . $user->getPassword();
        }
        $credential->setSecret($secret);
        $credential->setCreateTs(new DateTime());
        $credential->setId($user->getId());
        return $credential;
    }

}
