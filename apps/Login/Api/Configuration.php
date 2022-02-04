<?php
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

namespace KSA\Login\Api;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Legacy\Legacy;
use KSA\Register\ConfigProvider;
use KSP\Api\IResponse;
use KSP\App\ILoader;
use KSP\Core\Service\HTTP\IHTTPService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Configuration implements RequestHandlerInterface {

    private Legacy       $legacy;
    private IHTTPService $httpService;
    private ILoader      $loader;
    private InstanceDB   $instanceDB;

    public function __construct(
        Legacy         $legacy
        , IHTTPService $httpService
        , ILoader      $loader
        , InstanceDB   $instanceDB
    ) {
        $this->legacy      = $legacy;
        $this->httpService = $httpService;
        $this->loader      = $loader;
        $this->instanceDB  = $instanceDB;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $isDemoMode = $this->instanceDB->getOption("demo") === "true";
        $demo       = $isDemoMode
            ? md5(uniqid())
            : null;

        return new JsonResponse(
            [
                "appName"              => $this->legacy->getApplication()->get('name')
                , "newAccountLink"     => $this->httpService->getBaseURL(true) . "/register"
                , "forgotPasswordLink" => $this->httpService->getBaseURL(true) . "/forgot_password"
                , "registeringEnabled" => $this->loader->hasApp(ConfigProvider::APP_ID)

                // values
                , "backgroundPath"     => $this->httpService->getBaseURL(false) . "/asset/img/login-background.jpg"
                , "logoPath"           => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted_no_background.png"
                , "demo"               => $demo
                , "tncLink"            => $this->httpService->getBaseURL(true) . "/tnc/"
                , "demoMode"           => $isDemoMode
            ]
            , IResponse::OK
        );
    }

}