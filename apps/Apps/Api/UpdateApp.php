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

use Keestash\Api\Response\LegacyResponse;
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
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No Action Defined"
                ]
            );
        }

        if (false === $appId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No App Id Given"
                ]
            );
        }

        /** @var IApp|null $app */
        $app = $this->appRepository->getApp((string) $appId);

        if (null === $app) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No App Found"
                ]
            );
        }

        $app->setEnabled((bool) $activate);

        $replaced = $this->appRepository->replace($app);

        return LegacyResponse::fromData(
            $replaced ? IResponse::RESPONSE_CODE_OK : IResponse::RESPONSE_CODE_NOT_OK
            , [
                "message" => $replaced ? "App updated" : "No App Found"
            ]
        );

    }

}
