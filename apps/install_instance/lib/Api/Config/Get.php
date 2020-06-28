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

use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\Instance\InstallerService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\L10N\IL10N;

class Get extends AbstractApi {

    private $parameters       = null;
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
        $this->parameters = $parameters;

        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {

        $data = $this->installerService->verifyConfigurationFile();

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "config_data" => json_encode($data)
                , "strings"   => json_encode([
                    "dbHostLabel"                => $this->getL10N()->translate("Host")
                    , "dbHostPlaceholder"        => $this->getL10N()->translate("Host")
                    , "dbHostDescription"        => $this->getL10N()->translate("The server address where the database is hosted")
                    , "dbUserLabel"              => $this->getL10N()->translate("User")
                    , "dbUserPlaceholder"        => $this->getL10N()->translate("User")
                    , "dbUserDescription"        => $this->getL10N()->translate("The username used to connect to the database")
                    , "dbPasswordLabel"          => $this->getL10N()->translate("Password")
                    , "dbPasswordPlaceholder"    => $this->getL10N()->translate("Password")
                    , "dbPasswordDescription"    => $this->getL10N()->translate("The usernames password used to connect to the database")
                    , "dbNameLabel"              => $this->getL10N()->translate("Database")
                    , "dbNamePlaceholder"        => $this->getL10N()->translate("Database")
                    , "dbNameDescription"        => $this->getL10N()->translate("The database name")
                    , "dbPortLabel"              => $this->getL10N()->translate("Port")
                    , "dbPortPlaceholder"        => $this->getL10N()->translate("Port")
                    , "dbPortDescription"        => $this->getL10N()->translate("The port used to connect to the database")
                    , "dbCharsetLabel"           => $this->getL10N()->translate("Charset")
                    , "dbCharsetPlaceholder"     => $this->getL10N()->translate("Charset")
                    , "dbCharsetDescription"     => $this->getL10N()->translate("The databases charset")
                    , "logRequestsLabel"         => $this->getL10N()->translate("Log Requests")
                    , "enabledValue"             => $this->getL10N()->translate("enabled")
                    , "enabled"                  => $this->getL10N()->translate("enabled")
                    , "disabledValue"            => $this->getL10N()->translate("disabled")
                    , "disabled"                 => $this->getL10N()->translate("disabled")
                    , "dbLogRequestsDescription" => $this->getL10N()->translate("Whether API logs should be logged")
                    , "submit"                   => $this->getL10N()->translate("Save")
                    , "nothingToUpdate"          => $this->getL10N()->translate("Nothing To Update")
                    , "updated"                  => $this->getL10N()->translate("Updated Successfully")
                    , "utf8"                     => "utf8"
                ])
            ]
        );

    }

    public function afterCreate(): void {

    }

}
