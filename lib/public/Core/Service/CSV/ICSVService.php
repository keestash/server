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

namespace KSP\Core\Service\CSV;

use Keestash\Exception\File\FileNotFoundException;
use KSP\Core\Service\IService;
use League\Csv\Exception;
use League\Csv\InvalidArgument;

interface ICSVService extends IService {

    /**
     * @param string $path
     * @param bool   $hasOffset
     * @param string $delimiter
     * @return array
     * @throws FileNotFoundException
     * @throws Exception
     * @throws InvalidArgument
     */
    public function readFile(
        string   $path
        , bool   $hasOffset = true
        , string $delimiter = ','
    ): array;

    /**
     * @param string $content
     * @param bool   $hasOffset
     * @param string $delimiter
     * @return array
     * @throws Exception
     */
    public function readString(
        string   $content
        , bool   $hasOffset = true
        , string $delimiter = ','
    ): array;

}