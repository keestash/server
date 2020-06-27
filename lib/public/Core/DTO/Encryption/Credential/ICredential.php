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

namespace KSP\Core\DTO\Encryption\Credential;

use DateTime;
use KSP\Core\DTO\IObject;
use KSP\Core\DTO\User\IUser;

/**
 * Interface ICredential
 *
 * @package KSP\Core\DTO\Encryption\Credential
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
interface ICredential extends IObject {

    /**
     * Returns the Id identifying the credential uniquely
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Returns the credential's secret
     *
     * Depending on the type of implementation, the secret can
     * be encrypted or in plain
     *
     * @return string
     */
    public function getSecret(): string;

    /**
     * Returns the user to whom the credential belongs to
     *
     * @return IUser
     */
    public function getOwner(): IUser;

    /**
     * Returns the credential's creation date
     *
     * @return DateTime
     */
    public function getCreateTs(): DateTime;

}
