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

namespace KSA\PasswordManager\Event\Listener\AfterRegistration;

use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use KSA\PasswordManager\Exception\KeyNotCreatedException;
use KSA\PasswordManager\Exception\KeyNotStoredException;
use KSP\Core\DTO\User\IUser;

/**
 * Class CreateKey
 * @package KSA\PasswordManager\Hook\AfterRegistration
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class CreateKey {

    /** @var KeyService */
    private $keyService;

    /** @var CredentialService */
    private $credentialService;

    /**
     * CreateKey constructor.
     * @param KeyService        $keyService
     * @param CredentialService $credentialService
     */
    public function __construct(
        KeyService $keyService
        , CredentialService $credentialService
    ) {
        $this->keyService        = $keyService;
        $this->credentialService = $credentialService;
    }

    /**
     * @param IUser $user
     *
     * @throws KeyNotCreatedException
     * @throws KeyNotStoredException
     */
    public function run(IUser $user): void {
        // 1. create a key for the user
        $key = $this->keyService->createKey(
            $this->credentialService->getCredential($user)
            , $user
        );

        if (null === $key) {
            throw new KeyNotCreatedException();
        }

        $stored = $this->keyService->storeKey($user, $key);

        if (false === $stored) {
            throw new KeyNotStoredException();
        }

    }

}
