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

namespace Keestash\Core\Service\Router;

use Keestash\Core\DTO\Instance\Request\APIRequest;
use KSP\Api\IRequest;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Instance\IInstallerService;
use KSP\Core\Service\Router\IApiRequestService;
use KSP\Core\Service\Router\IRouterService;
use Psr\Http\Message\ServerRequestInterface;

class ApiRequestService implements IApiRequestService {

    private IEnvironmentService $environmentService;
    private IApiLogRepository   $apiLogRepository;
    private IInstallerService   $installerService;
    private IRouterService      $routerService;

    public function __construct(
        IEnvironmentService $environmentService
        , IApiLogRepository $apiLogRepository
        , IInstallerService $installerService
        , IRouterService    $routerService
    ) {
        $this->environmentService = $environmentService;
        $this->apiLogRepository   = $apiLogRepository;
        $this->installerService   = $installerService;
        $this->routerService      = $routerService;
    }

    public function log(ServerRequestInterface $request, float $end): void {
        if (false === $this->environmentService->isApi()) {
            return;
        }
        if (false === $this->installerService->hasIdAndHash()) {
            // we can not check for this, the instance is
            // not installed and there is no DB
            return;
        }

        if (true === $this->routerService->isPublicRoute($request)) {
            return;
        }

        $apiRequest = new APIRequest();
        $apiRequest->setStart((float) $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_START));
        $apiRequest->setEnd($end);
        $apiRequest->setRoute($this->routerService->getMatchedPath($request));
        $apiRequest->setToken($request->getAttribute(IToken::class));
        $this->apiLogRepository->log($apiRequest);
    }

}