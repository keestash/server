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

namespace KSP\Core\Service\Core\Locale;

use KSP\Core\DTO\User\IUser;

/**
 * Interface ILocaleService
 *
 * @package KSP\Core\Service\Core\Locale
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
interface ILocaleService {

    /**
     * Returns the locale for a given user stored in the settings
     *
     * @param IUser $user
     *
     * @return string
     */
    public function getLocaleForUser(IUser $user): string;

    /**
     * Returns the locale for the server
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Returns the primary language for the given user
     *
     * @param IUser $user
     * @return string
     */
    public function getPrimaryLanguageForUser(IUser $user): string;

}
