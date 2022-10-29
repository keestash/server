<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\InstallInstance\Command;

use Exception;
use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Core\Repository\File\IFileRepository;
use Psr\Log\LoggerInterface;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Config\Config;
use PDO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Install extends KeestashCommand {

    private const DEFAULT_USER_LIFETIME = 15 * 24 * 60 * 60;

    private Config                 $config;
    private LoggerInterface                $logger;
    private InstallerService       $installerService;
    private LockHandler            $lockHandler;
    private IUserRepositoryService $userRepositoryService;
    private IUserService           $userService;
    private IFileRepository        $fileRepository;
    private FileService            $fileService;

    public function __construct(
        Config                   $config
        , LoggerInterface                $logger
        , InstallerService       $installerService
        , LockHandler            $lockHandler
        , IUserRepositoryService $userRepositoryService
        , IUserService           $userService
        , IFileRepository        $fileRepository
        , FileService            $fileService
    ) {
        parent::__construct();
        $this->config                = $config;
        $this->logger                = $logger;
        $this->installerService      = $installerService;
        $this->lockHandler           = $lockHandler;
        $this->userRepositoryService = $userRepositoryService;
        $this->userService           = $userService;
        $this->fileRepository        = $fileRepository;
        $this->fileService           = $fileService;
    }

    protected function configure(): void {
        $this->setName("instance:install")
            ->setDescription("installs the instance");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->writeConfig($input, $output);
        $this->endUpdate();
        return 0;
    }

    private function endUpdate(): void {
        // TODO enhance logging
        $isInstalled = $this->installerService->isInstalled();

        if (false === $isInstalled) {
            throw new InstallInstanceException();
        }

        $ran     = $this->installerService->runCoreMigrations();
        $removed = $this->installerService->removeInstaller();
        $added   = $this->installerService->writeIdAndHash();
        $this->lockHandler->unlock();

        $this->userRepositoryService->createSystemUser(
            $this->userService->getSystemUser()
        );
        $defaultImage = $this->fileService->getDefaultImage();
        $defaultImage->setOwner(
            $this->userService->getSystemUser()
        );
        $this->fileRepository->add($defaultImage);

    }

    private function writeConfig(InputInterface $input, OutputInterface $output): void {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the following config");
        $host         = $style->ask("Host") ?? "";
        $user         = $style->ask("User") ?? "";
        $password     = $style->ask("Password") ?? "";
        $schemaName   = $style->ask("Schema Name") ?? "";
        $port         = $style->ask("Port") ?? "";
        $charSet      = $style->ask("Chart Set") ?? "";
        $logRequests  = $style->ask("Log Requests") ?? "disabled";
        $smtpHost     = $style->ask("SMTP Host") ?? "";
        $smtpUser     = $style->ask('SMTP User') ?? '';
        $smtpPassword = $style->ask('SMTP Password') ?? '';
        $hibpApiKey   = $style->ask('HIBP Api Key') ?? '';
        $sentryDsn    = $style->ask('Sentry DSN') ?? '';

        try {
            new PDO("mysql:host=$host;port=$port;dbname=$schemaName", $user, $password);
        } catch (Exception $exception) {
            $this->logger->error('error testing database connection', ['exception' => $exception]);
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
            , 'log_requests'    => $logRequests === ConfigProvider::LOG_REQUESTS_ENABLED ? true : false
            , "user_lifetime"   => Install::DEFAULT_USER_LIFETIME
            , "email_smtp_host" => $smtpHost
            , "email_user"      => $smtpUser
            , "email_password"  => $smtpPassword
            , "redis_server"    => '127.0.0.1'
            , "redis_port"      => 6379
            , "hibp_api_key"    => $hibpApiKey
            , "sentry_dsn"      => $sentryDsn
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
            throw new InstallInstanceException();
        }
    }

}
