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

use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;

class ResponseService {

    public function isValidResponse(ResponseInterface $response): bool {
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) return false;
        $body    = json_decode((string) $response->getBody(), true);
        $success = $body[IResponse::RESPONSE_CODE_OK] ?? null;
        return null !== $success;
    }

    public function getResponseData(ResponseInterface $response): array {
        if (false === $this->isValidResponse($response)) return [];
        $body = json_decode((string) $response->getBody(), true);
        return $body[IResponse::RESPONSE_CODE_OK]['messages'] ?? [];
    }

}