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
use Keestash\Core\Manager\StringManager\FrontendManager;
use KSP\Api\IResponse;
use KSP\Core\Cache\ICacheService;
use KSP\Core\Manager\StringManager\IStringManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetAll implements RequestHandlerInterface {

    private IStringManager $stringManager;
    private ICacheService  $cacheServer;

    public function __construct(
        FrontendManager $frontendManager
        , ICacheService $cacheServer
    ) {
        $this->stringManager = $frontendManager;
        $this->cacheServer   = $cacheServer;
    }

    private function getCachedStrings(string $key): array {

        if (true === $this->cacheServer->exists($key)) {
            return json_decode($this->cacheServer->get($key), true);
        }
        $data = $this->stringManager->load();
        $this->cacheServer->set($key, json_encode($data));
        return $data;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $redisKey = "frontendmanagertemplates";
        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "data" => $this->getCachedStrings($redisKey)
            ]
        );
    }

}
