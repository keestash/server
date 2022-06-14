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

namespace KSA\Register\Api\Configuration;

use Keestash\Api\Response\JsonResponse;
use Keestash\ConfigProvider;
use KSP\Api\IResponse;
use KSP\Core\Service\HTTP\IHTTPService;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Configuration implements RequestHandlerInterface {

    private Config       $config;
    private IHTTPService $httpService;

    public function __construct(
        Config         $config
        , IHTTPService $httpService
    ) {
        $this->config      = $config;
        $this->httpService = $httpService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return new JsonResponse(
            [
                'phoneConfig' => $this->config->get(ConfigProvider::COUNTRY_PREFIXES)->toArray()
                , 'tncLink'   => $this->httpService->getBaseURL(true, true) . '/login/'
                , 'loginLink' => $this->httpService->getBaseURL(true, true) . '/login/'
            ]
            , IResponse::OK
        );
    }

}