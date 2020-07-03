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

use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use KSP\Api\IResponse;
use KSP\App\Config\IApp;
use KSP\Core\DTO\IToken;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\L10N\IL10N;

class UpdateApp extends AbstractApi {

    private $parameters    = null;
    private $appRepository = null;

    public function __construct(
        IL10N $l10n
        , IAppRepository $appRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->appRepository = $appRepository;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $appId    = $this->parameters["app_id"] ?? null;
        $activate = $this->parameters["activate"] ?? null;


        if (null === $activate || false === is_bool($activate)) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No Action Defined"
                ]
            );

            parent::setResponse($response);
            return;
        }

        if (false === is_string($appId)) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No App Id Given"
                ]
            );

            parent::setResponse($response);
            return;
        }

        /** @var IApp|null $app */
        $app = $this->appRepository->getApp($appId);

        if (null === $app) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No App Found"
                ]
            );

            parent::setResponse($response);
            return;
        }

        $app->setEnabled($activate === IApp::ENABLED_TRUE);

        $replaced = $this->appRepository->replace($app);

        $response = parent::createResponse(
            $replaced ? IResponse::RESPONSE_CODE_OK : IResponse::RESPONSE_CODE_NOT_OK
            , [
                "message" => $replaced ? "App updated" : "No App Found"
            ]
        );

        parent::setResponse($response);
        return;


    }

    public function afterCreate(): void {

    }

}
