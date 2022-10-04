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

namespace KSA\ForgotPassword\Api;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\HTTP\IHTTPService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Configuration implements RequestHandlerInterface {

    private IHTTPService   $httpService;
    private ILoaderService $loader;

    public function __construct(
        IHTTPService     $httpService
        , ILoaderService $loader
    ) {
        $this->httpService = $httpService;
        $this->loader      = $loader;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return new JsonResponse(
            [
                "backToLoginLink"         => $this->httpService->getBaseURL(true) . "/login"
                , "newAccountLink"        => $this->httpService->getBaseURL(true) . "/register"
                , "forgotPasswordLink"    => $this->httpService->getBaseURL(true) . "/forgot_password"
                , "registeringEnabled"    => $this->loader->hasApp('register')
                , "forgotPasswordEnabled" => $this->loader->hasApp('forgot_password')
            ]
            , IResponse::OK
        );
    }

}