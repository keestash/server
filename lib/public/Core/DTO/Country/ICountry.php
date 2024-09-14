<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSP\Core\DTO\Country;

/**
 * copy from here if needed: https://justcall.io/app/country-code-information.html
 */
interface ICountry {

    public const string BELGIUM       = 'BE';
    public const string DENMARK       = 'DK';
    public const string FINLAND       = 'FI';
    public const string FRANCE        = 'FR';
    public const string GERMANY       = 'DE';
    public const string GREECE        = 'GR';
    public const string ITALY         = 'IT';
    public const string MONACO        = 'MC';
    public const string NETHERLANDS   = 'NL';
    public const string NORWAY        = 'NO';
    public const string TURKEY        = 'TR';
    public const string UNITED_STATES = 'US';
    public const string GREAT_BRITAIN = 'GB';

}
