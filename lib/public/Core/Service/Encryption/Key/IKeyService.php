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

namespace KSP\Core\Service\Encryption\Key;

use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;

interface IKeyService {

    /**
     * Returns an instance of IKey
     *
     * @param ICredential $credential
     * @param IKeyHolder  $keyHolder
     *
     * @return IKey|null
     */
    public function createKey(ICredential $credential, IKeyHolder $keyHolder): ?IKey;

    /**
     * Stores a given key
     *
     * @param IKeyHolder $keyHolder
     * @param IKey       $key
     * @return bool
     */
    public function storeKey(IKeyHolder $keyHolder, IKey $key): bool;

    /**
     * retrieves a given key
     *
     * @param IKeyHolder $keyHolder
     * @return IKey
     */
    public function getKey(IKeyHolder $keyHolder): IKey;

}