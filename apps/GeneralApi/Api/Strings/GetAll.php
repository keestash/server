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

namespace KSA\GeneralApi\Api\Strings;

use Keestash\Api\Response\LegacyResponse;
use KSP\Api\IResponse;
use KSP\Core\Cache\ICacheService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetAll implements RequestHandlerInterface {

    private ICacheService $cacheServer;

    public function __construct(ICacheService $cacheServer) {
        $this->cacheServer = $cacheServer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "data" => []
            ]
        );
    }

}
