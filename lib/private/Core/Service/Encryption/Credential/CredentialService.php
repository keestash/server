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
use KSP\Core\DTO\Encryption\Credential\IJsonCredential;
use KSP\Core\DTO\User\IJsonUser;

/**
 * Class CredentialService
 *
 * @package Keestash\Core\Service\Encryption\Credential
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class CredentialService {

    /**
     * Returns an instance of credential for the given user
     *
     * Please note that this credential is only used to de-/encrypt the
     * user's key. If you want to de-/encrypt user related stuff, try
     * encrypting with IKey.
     *
     * The Credential is used to encrypt the IKey. The content of IKey
     * is never changed, but the user's password may change over time.
     * When changing the password, it is only necessary to re-encrypt
     * the IKey and not the whole data encrypted so far.
     *
     * @param IJsonUser $user
     *
     * @return IJsonCredential
     */
    public function getCredentialForUser(IJsonUser $user): IJsonCredential {
        $credential = new Credential();
        $credential->setOwner($user);
        $credential->setSecret($user->getPassword());
        $credential->setCreateTs(new DateTime());
        $credential->setId($user->getId());
        return $credential;
    }

}
