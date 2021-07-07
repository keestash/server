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

namespace KSA\GeneralApi\Command\Migration;

use Keestash\Command\KeestashCommand;
use KSP\Core\Service\Phinx\IMigrator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateApps
 * @package Keestash\Command\Migration
 */
class MigrateApps extends KeestashCommand {

    protected static $defaultName = "general-api:migrate-apps";

    private const ARGUMENT_MODE = "mode";
    private const MODE_APP      = "app";
    private const MODE_INSTANCE = "instance";

    private IMigrator $migrator;

    public function __construct(IMigrator $migrator) {
        parent::__construct(MigrateApps::$defaultName);
        $this->migrator = $migrator;
    }

    protected function configure(): void {
        $this->setDescription("Runs all database migrations related to apps")
            ->setHelp("This command runs all migrations once you created a new phinx migration")
            ->addArgument(
                MigrateApps::ARGUMENT_MODE
                , InputArgument::REQUIRED
                , "Please provide a mode (instance, app)."
            );

    }

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $mode = $input->getArgument(MigrateApps::ARGUMENT_MODE);
        $ran  = false;

        if (false === in_array($mode, [MigrateApps::MODE_INSTANCE, MigrateApps::MODE_APP])) {
            $this->writeError(
                'given mode is ' . $mode . '. You need to pass either app or instance as an argument'
                , $output
            );
            return KeestashCommand::RETURN_CODE_INVALID_ARGUMENT;
        }
        if ($mode === MigrateApps::MODE_INSTANCE) {
            $ran = $this->migrator->runCore();
        }

        if ($mode === MigrateApps::MODE_APP) {
            $ran = $this->migrator->runApps();
        }

        if (false === $ran) {
            $this->writeError(
                'migration did not ran. Please check the error logs for further information'
                , $output
            );
            return KeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
        }

        $this->writeInfo(
            'Everything went fine :-)'
            , $output
        );

        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
