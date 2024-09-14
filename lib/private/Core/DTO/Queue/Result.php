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

namespace Keestash\Core\DTO\Queue;

use KSP\Core\DTO\Queue\IResult;

class Result implements IResult {

    private int $code;

    /**
     * @return int
     */
    #[\Override]
    public function getCode(): int {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void {
        $this->code = $code;
    }

    public static function getOk(): IResult {
        $result = new Result();
        $result->setCode(IResult::RETURN_CODE_OK);
        return $result;
    }

    public static function getNotOk(): IResult {
        $result = new Result();
        $result->setCode(IResult::RETURN_CODE_NOT_OK);
        return $result;
    }

}