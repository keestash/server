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

use Exception;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\Instance\InstallerService;
use KSA\InstallInstance\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use PDO;

/**
 * Class UpdateConfig
 * @package KSA\InstallInstance\Api
 */
class Update extends AbstractApi {

    private const DEFAULT_USER_LIFETIME = 15 * 24 * 60 * 60;

    private InstallerService $installerService;

    public function __construct(
        IL10N $l10n
        , InstallerService $installerService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->installerService = $installerService;
    }

    public function onCreate(array $parameters): void {
        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {

        $host         = $this->getParameter("host", null);
        $user         = $this->getParameter("user", null);
        $password     = $this->getParameter("password", null);
        $schemaName   = $this->getParameter("schema_name", null);
        $port         = $this->getParameter("port", null);
        $charSet      = $this->getParameter("charset", null);
        $logRequests  = $this->getParameter("log_requests", null);
        $smtpHost     = $this->getParameter("smtp_host", null);
        $smtpUser     = $this->getParameter("smtp_user", null);
        $smtpPassword = $this->getParameter("smtp_password", null);

        if (
            false === $this->isValid($host)
            || false === $this->isValid($user)
            || null === $password
            || false === $this->isValid($schemaName)
            || false === $this->isValid($port)
            || false === $this->isValid($charSet)
            || false === $this->validLogRequestOption($logRequests)
        ) {

            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    'invalid options' => [
                        "host"           => $host
                        , "user"         => $user
                        , "password"     => $password
                        , "schema_name"  => $schemaName
                        , "port"         => $port
                        , "charset"      => $charSet
                        , "log_requests" => $logRequests
                    ]
                ]
            );
            return;

        }

        $databaseConnection = $this->testDatabaseConnection(
            $host
            , $schemaName
            , $user
            , $password
            , $port
        );

        if (false === $databaseConnection) {

            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not connect to database"
                ]
            );
            return;

        }

        $config = [
            'show_errors'       => false
            , 'debug'           => false
            , 'db_host'         => $host
            , 'db_user'         => $user
            , 'db_password'     => $password
            , 'db_name'         => $schemaName
            , 'db_port'         => $port
            , 'db_charset'      => $charSet
            , 'log_requests'    => $logRequests === Application::LOG_REQUESTS_ENABLED ? true : false
            , "user_lifetime"   => Update::DEFAULT_USER_LIFETIME
            , "email_smtp_host" => $smtpHost
            , "email_user"      => $smtpUser
            , "email_password"  => $smtpPassword
        ];

        $configRoot = Keestash::getServer()->getConfigRoot();
        $content    = '<?php' . "\n";
        $content    .= 'declare(strict_types=1);' . "\n";
        $content    .= '/**
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
' . "\n\n\n";
        $content    .= '$CONFIG = ' . "\n";
        $content    .= var_export($config, true);
        $content    .= ';';
        $configFile = realpath($configRoot . "/config.php");

        $put = file_put_contents(
            $configFile
            , $content
        );

        if (false === $put) {
            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not create config file. Please check permissiosn and try again"
                ]
            );
            return;
        }

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => "updated"
            ]
        );

    }

    private function validLogRequestOption($val): bool {
        if (false === $this->isValid($val)) return false;

        if ($val === Application::LOG_REQUESTS_ENABLED) return true;
        if ($val === Application::LOG_REQUESTS_DISABLED) return true;

        return false;
    }

    private function isValid($val): bool {
        if (null === $val || "" === trim($val)) return false;
        return true;
    }

    private function testDatabaseConnection(
        string $host
        , string $schemaName
        , string $user
        , string $password
        , string $port
    ): bool {
        try {
            new PDO("mysql:host=$host;port=$port;dbname=$schemaName", $user, $password);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function afterCreate(): void {

    }

}
