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

use KSP\Core\DTO\Country\ICountry;
use KSP\Core\DTO\Country\IPrefix;

return [
    ICountry::GERMANY         => IPrefix::GERMANY
    , ICountry::UNITED_STATES => IPrefix::UNITED_STATES
    , ICountry::GREAT_BRITAIN => IPrefix::GREAT_BRITAIN
    , ICountry::BELGIUM       => IPrefix::BELGIUM
    , ICountry::DENMARK       => IPrefix::DENMARK
    , ICountry::FINLAND       => IPrefix::FINLAND
    , ICountry::FRANCE        => IPrefix::FRANCE
    , ICountry::GREECE        => IPrefix::GREECE
    , ICountry::ITALY         => IPrefix::ITALY
    , ICountry::MONACO        => IPrefix::MONACO
    , ICountry::NETHERLANDS   => IPrefix::NETHERLANDS
    , ICountry::NORWAY        => IPrefix::NORWAY
    , ICountry::TURKEY        => IPrefix::TURKEY
];