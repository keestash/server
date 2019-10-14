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

use doganoo\PHPUtil\Log\FileLogger;
use Exception;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\InstallerService;
use Keestash\Core\System\Installation\Verification\ConfigFileReadable;
use Keestash\Core\System\Installation\Verification\DatabaseReachable;
use KSA\InstallInstance\Application\Application;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;
use PDO;

/**
 * Class UpdateConfig
 * @package KSA\InstallInstance\Api
 */
class UpdateConfig extends AbstractApi {

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

        $host        = $this->parameters["host"] ?? null;
        $user        = $this->parameters["user"] ?? null;
        $password    = $this->parameters["password"] ?? null;
        $schemaName  = $this->parameters["schema_name"] ?? null;
        $port        = $this->parameters["port"] ?? null;
        $charSet     = $this->parameters["charset"] ?? null;
        $logRequests = $this->parameters["log_requests"] ?? null;

        if (
            false === $this->isValid($host) ||
            false === $this->isValid($user) ||
            null === $password ||
            false === $this->isValid($schemaName) ||
            false === $this->isValid($port) ||
            false === $this->isValid($charSet) ||
            false === $this->validLogRequestOption($logRequests)
        ) {

            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => [
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
            'show_errors'    => false
            , 'debug'        => false
            , 'db_host'      => $host
            , 'db_user'      => $user
            , 'db_password'  => $password
            , 'db_name'      => $schemaName
            , 'db_port'      => $port
            , 'db_charset'   => $charSet
            , 'log_requests' => $logRequests === Application::LOG_REQUESTS_ENABLED ? true : false
        ];

        $configRoot = Keestash::getServer()->getConfigRoot();
        $content    = '<?php' . PHP_EOL;
        $content    .= 'declare(strict_types=1);' . PHP_EOL;
        $content    .= '/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or indirectly through a Keestash authorized reseller or distributor (a "Reseller").
 * Please read this EULA agreement carefully before completing the installation process and using the Keestash software. It provides a license to use the Keestash software and contains warranty information and liability disclaimers.
 */
' . PHP_EOL;
        $content    .= '$CONFIG = ' . PHP_EOL;
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

        $updated = $this->installerService->updateInstaller(ConfigFileReadable::class);

        if (false === $updated) {
            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not update installer file. Please try again"
                ]
            );
            return;
        }

        $updated = $this->installerService->updateInstaller(DatabaseReachable::class);

        if (false === $updated) {
            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not update installer file. Please try again (2)"
                ]
            );
            return;
        }

        $ran = $this->installerService->runCoreMigrations();

        FileLogger::debug("is there an db connection: $databaseConnection");
        FileLogger::debug("migrations ran: $ran");

        if (false === $ran) {

            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not create dataabases"
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
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$schemaName", $user, $password);
            return true;
        } catch (Exception $e) {
            FileLogger::info("test database connection is false {$e->getTraceAsString()}");
            return false;
        }
    }

    public function afterCreate(): void {

    }

}