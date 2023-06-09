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

namespace Keestash\Core\Service\CSV;

use Keestash\Exception\File\FileNotFoundException;
use KSP\Core\Service\CSV\ICSVService;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;

class CSVService implements ICSVService {

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
    ): array {
        if (false === file_exists($path) || false === is_file($path)) {
            throw new FileNotFoundException();
        }
        $csv = Reader::createFromPath($path);

        if (true === $hasOffset) {
            $csv->setHeaderOffset(0);
        }

        $csv->setDelimiter($delimiter);
        return iterator_to_array($csv->getRecords());
    }

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
    ): array {
        $csv = Reader::createFromString($content);

        if (true === $hasOffset) {
            $csv->setHeaderOffset(0);
        }
        $csv->setDelimiter($delimiter);
        return iterator_to_array($csv->getRecords());
    }

}