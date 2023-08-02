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

namespace KSP\Core\Service\Encryption\Credential;

use Keestash\Exception\Repository\Derivation\DerivationException;
use Keestash\Exception\User\UserException;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\Service\IService;

interface ICredentialService extends IService {

    /**
     * @param IKeyHolder $keyHolder
     * @return ICredential
     * @throws DerivationException
     * @throws UserException
     */
    public function createCredential(IKeyHolder $keyHolder): ICredential;

    /**
     * @param IKeyHolder $keyHolder
     * @return ICredential
     * @throws UserException
     */
    public function createCredentialFromDerivation(IKeyHolder $keyHolder): ICredential;

}