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
use Keestash\ConfigProvider as CoreConfigProvider;
use KSP\Api\IResponse;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Configuration implements RequestHandlerInterface {

    public function __construct(private readonly Config $config)
    {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        return new JsonResponse(
            [
                'phoneConfig'       => $this->config->get(ConfigProvider::COUNTRY_PREFIXES)->toArray()
                , 'registerEnabled' => true === $request->getAttribute(ConfigProvider::REGISTER_ENABLED, false)
                , 'resetEnabled'    => true === $request->getAttribute(ConfigProvider::ACCOUNT_RESET_ENABLED, false)
                , 'isSaas'          => $request->getAttribute(CoreConfigProvider::ENVIRONMENT_SAAS)
            ]
            , IResponse::OK
        );
    }

}