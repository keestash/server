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

namespace Keestash\Api\Response;

use Keestash\Core\DTO\HTTP;
use KSP\Api\IResponse;
use function json_encode;

class DefaultResponse implements IResponse {

    private $code     = null;
    private $messages = null;
    private $header   = null;

    public function __construct() {
        $this->code     = HTTP::OK;
        $this->messages = [];
        $this->header   = [];
    }

    public function addMessage(int $code, array $messages): void {
        $this->messages[$code] = $messages;
    }

    public function getMessage(): ?string {
        $json = json_encode($this->messages);
        return (false === $json) ? null : $json;
    }

    public function getDescription(): string {
        return HTTP::getDescriptionByCode($this->getCode());
    }

    public function getCode(): int {
        return $this->code;
    }

    public function setCode(int $code): void {
        $this->code = $code;
    }

    public function addHeader(string $name, string $value): void {
        $this->header[$name] = $value;
    }

    public function getHeaders(): array {
        return $this->header;
    }

}