<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Command\Install;

use Exception;
use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Exception\File\FileNotCreatedException;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateConfig extends KeestashCommand {

    public const DEFAULT_USER_LIFETIME = 15 * 24 * 60 * 60;
    public const OPTION_NAME_FORCE     = 'force';

    public function __construct(
        private readonly Config            $config
        , private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("keestash:install:config")
            ->setDescription("writes the instance config file")
            ->addOption(
                CreateConfig::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE
                , 'whether to force recreation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force      = (bool) $input->getOption(CreateConfig::OPTION_NAME_FORCE);
        $configFile = realpath($this->config->get(ConfigProvider::CONFIG_PATH) . "/config.php");
        if (true === is_file((string) $configFile) || false === $force) {
            $overwrite = $this->confirmQuestion('A config file exists. Do you want to regenerate?', $input, $output);
            if (false === $overwrite) {
                $this->writeInfo('aborting', $output);
                return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
            }
        }
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the following config");
        $host         = (string) $style->ask("Host");
        $user         = (string) $style->ask("User");
        $password     = (string) $style->ask("Password");
        $schemaName   = (string) $style->ask("Schema Name");
        $port         = (string) $style->ask("Port");
        $charSet      = (string) $style->ask("Chart Set");
        $logRequests  = (string) ($style->ask("Log Requests") ?? "disabled");
        $smtpHost     = (string) $style->ask("SMTP Host");
        $smtpUser     = (string) $style->ask('SMTP User');
        $smtpPassword = (string) $style->ask('SMTP Password');
        $hibpApiKey   = (string) $style->ask('HIBP Api Key');
        $sentryDsn    = (string) $style->ask('Sentry DSN');

        try {
            new PDO("mysql:host=$host;port=$port;dbname=$schemaName", $user, $password);
        } catch (Exception $exception) {
            $this->logger->error('error testing database connection', ['exception' => $exception]);
            return IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
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
            , "user_lifetime"   => CreateConfig::DEFAULT_USER_LIFETIME
            , "email_smtp_host" => $smtpHost
            , "email_user"      => $smtpUser
            , "email_password"  => $smtpPassword
            , "redis_server"    => '127.0.0.1'
            , "redis_port"      => 6379
            , "hibp_api_key"    => $hibpApiKey
            , "sentry_dsn"      => $sentryDsn
        ];

        $content = '<?php' . "\n";
        $content .= 'declare(strict_types=1);' . "\n";
        $content .= '/**
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
        $content .= '$CONFIG = ' . "\n";
        $content .= var_export($config, true);
        $content .= ';';

        $put = file_put_contents(
            (string) $configFile
            , $content
        );

        if (false === $put) {
            throw new FileNotCreatedException();
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}