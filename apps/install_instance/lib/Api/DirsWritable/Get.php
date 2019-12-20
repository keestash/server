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

namespace KSA\InstallInstance\Api\DirsWritable;

use doganoo\PHPUtil\Log\FileLogger;
use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\InstallerService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\L10N\IL10N;

class Get extends AbstractApi {

    private $installerService = null;

    public function __construct(
        IL10N $l10n
        , InstallerService $installerService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->installerService = $installerService;
    }

    public function onCreate(array $parameters): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $writableDirs = $this->installerService->verifyWritableDirs();

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "writable_dirs" => json_encode($writableDirs)
                , "strings"     => json_encode([
                    "nothingToUpdate"       => $this->getL10N()->translate("Nothing To Update")
                    , "updated"             => $this->getL10N()->translate("updated")
                    , "submitText"          => $this->getL10N()->translate("Try Again")
                    , "writableHeader"      => $this->getL10N()->translate("Files / Directories that are not writable")
                    , "writableDescription" => $this->getL10N()->translate("The following files are not writable")
                ])

            ]
        );
    }

    public function afterCreate(): void {

    }

}