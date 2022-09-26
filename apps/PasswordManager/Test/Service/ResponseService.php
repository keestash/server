<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Service;

use Psr\Http\Message\ResponseInterface;

class ResponseService {

    public function isValidResponse(ResponseInterface $response): bool {
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) return false;
        return true;
    }

    public function getResponseData(ResponseInterface $response): array {
        if (false === $this->isValidResponse($response)) return [];
        return json_decode((string) $response->getBody(), true, JSON_THROW_ON_ERROR);
    }

    public function getFailedResponseData(ResponseInterface $response): array {
        if (true === $this->isValidResponse($response)) return [];
        return json_decode((string) $response->getBody(), true, JSON_THROW_ON_ERROR);
    }

}