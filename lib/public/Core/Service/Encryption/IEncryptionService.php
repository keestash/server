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

namespace KSP\Core\Service\Encryption;

use Keestash\Exception\EncryptionFailedException;
use KSP\Core\DTO\Encryption\Credential\ICredential;

interface IEncryptionService {

    public function encrypt(ICredential $credential, string $raw): string;

    /**
     * @param ICredential $credential
     * @param string      $encrypted
     * @return string
     * @throws EncryptionFailedException
     */
    public function decrypt(ICredential $credential, string $encrypted): string;

}
