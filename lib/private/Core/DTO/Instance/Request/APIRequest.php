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

namespace Keestash\Core\DTO\Instance\Request;

use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\Token\IToken;

/**
 * Class APIRequest
 * @package Keestash\Core\DTO
 */
final readonly class APIRequest implements IAPIRequest {

    public function __construct(
        private IToken $token,
        private float  $start,
        private float  $end,
        private string $route
    ) {
    }

    public function getToken(): IToken {
        return $this->token;
    }

    public function getRoute(): string {
        return $this->route;
    }

    public function getDuration(): int {
        return (int) abs($this->getEnd() - $this->getStart());
    }

    public function getEnd(): float {
        return $this->end;
    }

    public function getStart(): float {
        return $this->start;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return [
            "token"      => $this->getToken()
            , "route"    => $this->getRoute()
            , "duration" => $this->getDuration()
            , "start"    => $this->getStart()
            , "end"      => $this->getEnd()
        ];
    }

}
