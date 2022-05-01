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

namespace KSA\InstallInstance\Api\Config;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\Instance\InstallerService;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    private InstallerService $installerService;

    public function __construct(InstallerService $installerService) {
        $this->installerService = $installerService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $data = $this->installerService->verifyConfigurationFile();
        return new JsonResponse(
            [
                "config_data" => json_encode($data)
                , "length"    => count($data)
            ]
            , IResponse::OK
        );
    }

}
