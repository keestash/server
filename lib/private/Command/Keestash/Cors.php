<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Command\Keestash;

use DateTimeImmutable;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\KeestashCommand;
use Keestash\Exception\File\FileNotFoundException;
use KSP\Command\IKeestashCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Cors extends KeestashCommand {

    public const OPTION_NAME_FORCE_WRITE       = 'force-write';
    public const OPTION_NAME_SKIP_FORCE_BACKUP = 'force-backup';
    public const ARGUMENT_NAME_HOSTS           = 'hosts';

    public function __construct(
        private readonly IDateTimeService $dateTimeService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("keestash:cors")
            ->setDescription("sets the cors hosts")
            ->addOption(
                Cors::OPTION_NAME_FORCE_WRITE
                , 'f'
                , InputOption::VALUE_NONE
            )
            ->addOption(
                Cors::OPTION_NAME_SKIP_FORCE_BACKUP
                , 's'
                , InputOption::VALUE_NONE
            )
            ->addArgument(
                Cors::ARGUMENT_NAME_HOSTS
                , InputArgument::REQUIRED | InputArgument::IS_ARRAY
                , 'the allowed hosts for cors'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $forceWrite   = true === $input->getOption(Cors::OPTION_NAME_FORCE_WRITE);
        $forceBackup  = true === $input->getOption(Cors::OPTION_NAME_SKIP_FORCE_BACKUP);
        $hosts        = (array) $input->getArgument(Cors::ARGUMENT_NAME_HOSTS);
        $destination  = realpath(__DIR__ . '/../../../config/cors/allowed_origins.php');

        if (false === $destination) {
            throw new FileNotFoundException();
        }
        $configExists = file_exists($destination);

        if (true === $configExists) {
            $backup = $forceBackup || $this->confirmQuestion('do you want to backup the existing file?', $input, $output);

            if (true === $backup) {
                $backupFile       = dirname($destination) . '/allowed_origins.bak';
                $backupFileExists = file_exists($backupFile);
                if (true === $backupFileExists) {
                    unlink($backupFile);
                }
                $copied = copy($destination, dirname($destination) . '/allowed_origins.bak');
                if (false === $copied) {
                    $this->writeError('could not backup', $output);
                }
            }
        }

        if ($configExists && false === $forceWrite) {
            $overwrite = $this->confirmQuestion('do you really want to overwrite?', $input, $output);
            if (false === $overwrite) {
                $this->writeInfo('aborting', $output);
                return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
            }
            unlink($destination);
        }

        file_put_contents(
            $destination
            , $this->getData($hosts)
        );
        $output->writeln(
            sprintf(
                "the following hosts are set: %s"
                , implode(', ', $hosts)
            )
        );
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function getData(array $hosts): string {

        $result = '';
        $year   = (new DateTimeImmutable())->format('Y');

        foreach ($hosts as $host) {
            $result = $result . "'$host',";
        }
        return '<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <' . $year . '> <Dogan Ucar>
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
 *
 ' . sprintf('* This file was generated by %s at %s', __FILE__, $this->dateTimeService->toYMDHIS(new DateTimeImmutable())) . ' 
 *
 */

return [
    ' . $result . '
];
        ';
    }

}