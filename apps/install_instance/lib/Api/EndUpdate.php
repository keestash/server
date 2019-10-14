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

namespace KSA\InstallInstance\Api;

use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\Manager\RouterManager\Router\Helper;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\InstallerService;
use Keestash\Core\System\Installation\Instance\HealthCheck;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;

class EndUpdate extends AbstractApi {

    private $parameters       = null;
    private $installerService = null;
    private $healthCheck      = null;

    public function __construct(
        IL10N $l10n
        , InstallerService $installerService
        , HealthCheck $healthCheck
    ) {
        parent::__construct($l10n, true);

        $this->installerService = $installerService;
        $this->healthCheck      = $healthCheck;
    }

    public function onCreate(...$params): void {
        $this->parameters = $params[0];

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $isInstalled = $this->installerService->isEmpty();

        if (true === $isInstalled) {
            $this->installerService->removeInstaller();
            $this->healthCheck->storeInstallation();

            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_OK
                , [
                    "message"    => "Ok"
                    , "route_to" => Helper::buildWebRoute(
                        Keestash::getServer()->getAppLoader()->getDefaultApp()->getBaseRoute()
                    )
                ]
            );
            return;
        }

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_NOT_OK
            , [
                "message" => "is still not installed"
            ]
        );

    }

    public function afterCreate(): void {
        // TODO: Implement afterCreate() method.
    }

}