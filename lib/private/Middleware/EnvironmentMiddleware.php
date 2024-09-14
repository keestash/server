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

namespace Keestash\Middleware;

use Keestash\ConfigProvider;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSA\Settings\Service\ISettingsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class EnvironmentMiddleware implements MiddlewareInterface {

    public function __construct(
        private InstanceDB         $instanceDb
        , private ISettingsService $settingsService
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $isSaas          = 'true' === $this->instanceDb->getOption(InstanceDB::OPTION_NAME_SAAS);
        $registerEnabled = $this->settingsService->isRegisterEnabled();

        $request = $request->withAttribute(
            ConfigProvider::ENVIRONMENT_SAAS,
            $isSaas
        );
        $request = $request->withAttribute(
            ConfigProvider::REGISTER_ENABLED
            , $registerEnabled
        );
        $request = $request->withAttribute(
            ConfigProvider::ACCOUNT_RESET_ENABLED
            , $registerEnabled
        );
        return $handler->handle($request);
    }

}
