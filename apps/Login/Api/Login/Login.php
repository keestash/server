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

namespace KSA\Login\Api\Login;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Api\Version\IVersion;
use KSP\Core\Service\Metric\ICollectorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly final class Login implements RequestHandlerInterface {

    public function __construct(
        private Alpha               $alpha
        , private Beta              $beta
        , private ICollectorService $collector
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IVersion $version */
        $version = $request->getAttribute(IVersion::class);

        $response = match ($version->getVersion()) {
            1 => $this->alpha->handle($request),
            2 => $this->beta->handle($request),
            default => new JsonResponse([], IResponse::NOT_FOUND),
        };

        $this->collector->addCounter(
            $response->getStatusCode() === IResponse::OK
                ? 'loginsuccessfull'
                : 'errorlogin'
        );
        return $response;
    }

}
