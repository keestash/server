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

use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\InstallerService;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;

class DirsWritable extends AbstractApi {

    private $parameters       = null;
    private $installerService = null;

    public function __construct(
        IL10N $l10n
        , InstallerService $installerService
    ) {
        parent::__construct($l10n, true);

        $this->installerService = $installerService;
    }

    public function onCreate(...$params): void {
        $this->parameters = $params[0];

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $dirsWritable = new \Keestash\Core\System\Installation\Verification\DirsWritable();
        $writable     = $dirsWritable->hasProperty();

        $messages = $dirsWritable->getMessages();
        $messages = $messages[\Keestash\Core\System\Installation\Verification\DirsWritable::class];

        if (false === $writable) {

            $this->installerService->updateInstaller(\Keestash\Core\System\Installation\Verification\DirsWritable::class);

            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , $messages
            );
            return;
        }

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "messages" => "Ok"
            ]
        );
    }

    public function afterCreate(): void {

    }

}