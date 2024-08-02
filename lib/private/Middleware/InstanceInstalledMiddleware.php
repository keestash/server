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

namespace Keestash\Middleware;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class InstanceInstalledMiddleware implements MiddlewareInterface {

    public function __construct(
        private InstanceDB        $instanceDB
        , private LoggerInterface $logger
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $routesToInstallation = $request->getAttribute(IRequest::ATTRIBUTE_NAME_ROUTES_TO_INSTANCE_INSTALL, false);
        $instanceHash         = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH);
        $instanceId           = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_ID);

        if (true === $routesToInstallation) {
            return $handler->handle($request);
        }

        if ((null === $instanceHash || null === $instanceId)) {
            $this->logger->debug("The whole application is not installed. Please Install", ['hash' => $instanceHash, 'id' => $instanceId]);
            return new JsonResponse(
                [
                    'responseCode' => 48599
                ],
                IResponse::SERVICE_UNAVAILABLE
            );
        }

        return $handler->handle($request);
    }

}
