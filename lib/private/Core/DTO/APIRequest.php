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

namespace Keestash\Core\DTO;

use KSP\Core\DTO\IAPIRequest;
use KSP\Core\DTO\IToken;

/**
 * Class APIRequest
 * @package Keestash\Core\DTO
 */
class APIRequest implements IAPIRequest {

    private $token = null;
    private $start = null;
    private $end   = null;
    private $route = null;

    public function getToken(): IToken {
        return $this->token;
    }

    public function setToken(IToken $token): void {
        $this->token = $token;
    }

    public function getRoute(): string {
        return $this->route;
    }

    public function setRoute(string $route): void {
        $this->route = $route;
    }

    public function getDuration(): int {
        return abs($this->getEnd() - $this->getStart());
    }

    public function getEnd(): float {
        return $this->end;
    }

    public function setEnd(float $end): void {
        $this->end = $end;
    }

    public function getStart(): float {
        return $this->start;
    }

    public function setStart(float $start): void {
        $this->start = $start;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            "token"      => $this->getToken()
            , "route"    => $this->getRoute()
            , "duration" => $this->getDuration()
            , "start"    => $this->getStart()
            , "end"      => $this->getEnd()
        ];
    }

}