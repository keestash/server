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

namespace KSA\Apps\Api;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateApp implements RequestHandlerInterface {

    private IAppRepository $appRepository;

    public function __construct(IAppRepository $appRepository) {
        $this->appRepository = $appRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $appId      = $parameters["app_id"] ?? null;
        $activate   = $parameters["activate"] ?? null;

        if (null === $activate) {
            return new JsonResponse([
                    "message" => "No Action Defined"
                ]
                , IResponse::BAD_REQUEST
            );
        }

        if (false === $appId) {
            return new JsonResponse([
                    "message" => "No App Id Given"
                ]
                , IResponse::BAD_REQUEST
            );
        }

        /** @var IApp|null $app */
        $app = $this->appRepository->getApp((string) $appId);

        if (null === $app) {
            return new JsonResponse([
                    "message" => "No App Found"
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $app->setEnabled((bool) $activate);

        $replaced = $this->appRepository->replace($app);

        return new JsonResponse(
            [
                "message" => $replaced ? "App updated" : "No App Found"
            ]
            , $replaced ? IResponse::OK : IResponse::INTERNAL_SERVER_ERROR
        );

    }

}
