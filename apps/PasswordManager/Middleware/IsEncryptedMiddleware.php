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

namespace KSA\PasswordManager\Middleware;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class IsEncryptedMiddleware implements MiddlewareInterface {

    private const array FIELD_NAMES = [
        'username',
        'url',
        'password',
    ];

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $allParameters =
            $request->getMethod() === IVerb::GET
                ? array_merge(
                $request->getQueryParams(),
                $request->getAttributes()
            )
                : $request->getParsedBody();
        $canPass       = $this->canPass((array) $allParameters);

        if (false === $canPass) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }
        return $handler->handle($request);
    }

    private function canPass(array $allParameters): bool {
        foreach ($allParameters as $name => $value) {
            if (false === in_array(strtolower($name), IsEncryptedMiddleware::FIELD_NAMES)) {
                continue;
            }

            if (false === $this->isLikelyEncrypted($value)) {
                return false;
            }
        }
        return true;
    }

    private function isLikelyEncrypted(string $value): bool {
        $decoded         = base64_decode($value, true);
        $isBase64Encoded = $decoded !== false && base64_encode($decoded) === $value;

        if (false === $isBase64Encoded) {
            return false;
        }

        $hasValidAesBlockSize = strlen($decoded) % 16 === 0;
        if (false === $hasValidAesBlockSize) {
            return false;
        }

        $entropyThreshold = 4.5;
        $entropy          = $this->calculateEntropy($decoded);
        if ($entropy <= $entropyThreshold) {
            return false;
        }

        return true;
    }

    private function calculateEntropy(string $data): float {
        $h    = 0;
        $size = strlen($data);

        foreach (count_chars($data, 1) as $frequency) {
            $p = $frequency / $size;
            $h -= $p * log($p) / log(2);
        }

        return $h;
    }

}
