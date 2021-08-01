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
use Keestash\Api\Response\LegacyResponse;
use Keestash\ConfigProvider;
use Keestash\Core\Service\Instance\InstallerService;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Api\IResponse;
use Laminas\Config\Config;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class UpdateConfig
 * @package KSA\InstallInstance\Api
 */
class Update implements RequestHandlerInterface {

    private const DEFAULT_USER_LIFETIME = 15 * 24 * 60 * 60;

    private InstallerService $installerService;
    private Config           $config;

    public function __construct(
        InstallerService $installerService
        , Config $config
    ) {
        $this->installerService = $installerService;
        $this->config           = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = json_decode((string) $request->getBody(), true);
        $host         = $parameters["host"] ?? null;
        $user         = $parameters["user"] ?? null;
        $password     = $parameters["password"] ?? null;
        $schemaName   = $parameters["schema_name"] ?? null;
        $port         = $parameters["port"] ?? null;
        $charSet      = $parameters["charset"] ?? null;
        $logRequests  = $parameters["log_requests"] ?? null;
        $smtpHost     = $parameters["smtp_host"] ?? null;
        $smtpUser     = $parameters["smtp_user"] ?? null;
        $smtpPassword = $parameters["smtp_password"] ?? null;

        if (
            false === $this->isValid($host)
            || false === $this->isValid($user)
            || null === $password
            || false === $this->isValid($schemaName)
            || false === $this->isValid($port)
            || false === $this->isValid($charSet)
            || false === $this->validLogRequestOption($logRequests)
        ) {

            return LegacyResponse::fromData(
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

        }

        $databaseConnection = $this->testDatabaseConnection(
            $host
            , $schemaName
            , $user
            , $password
            , $port
        );

        if (false === $databaseConnection) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not connect to database"
                ]
            );

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
            , 'log_requests'    => $logRequests === ConfigProvider::LOG_REQUESTS_ENABLED ? true : false
            , "user_lifetime"   => Update::DEFAULT_USER_LIFETIME
            , "email_smtp_host" => $smtpHost
            , "email_user"      => $smtpUser
            , "email_password"  => $smtpPassword
            , "redis_server"    => '127.0.0.1'
            , "redis_port"      => 6379
        ];

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
        $configFile = realpath($this->config->get(ConfigProvider::CONFIG_PATH) . "/config.php");

        if (false === $configFile) {
            throw new InstallInstanceException();
        }

        $put = file_put_contents(
            $configFile
            , $content
        );

        if (false === $put) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not create config file. Please check permissiosn and try again"
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => "updated"
            ]
        );

    }

    private function validLogRequestOption($val): bool {
        if (false === $this->isValid($val)) return false;

        if ($val === ConfigProvider::LOG_REQUESTS_ENABLED) return true;
        if ($val === ConfigProvider::LOG_REQUESTS_DISABLED) return true;

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

}
