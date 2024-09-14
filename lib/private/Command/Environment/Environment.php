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

namespace Keestash\Command\Environment;

use Keestash\Command\KeestashCommand;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Command\IKeestashCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Environment extends KeestashCommand {

    public const string OPTION_NAME_FORCE   = 'force';
    public const string ARGUMENT_NAME_NAME  = 'name';
    public const string ARGUMENT_NAME_VALUE = 'value';

    public function __construct(
        private readonly InstanceDB $instanceDB
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("environment:add")
            ->setDescription("sets the app environment")
            ->setAliases(["keestash:environment:add"])
            ->addOption(
                Environment::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_NONE
            )
            ->addArgument(
                Environment::ARGUMENT_NAME_NAME
                , InputArgument::REQUIRED
                , 'the environment name'
            )
            ->addArgument(
                Environment::ARGUMENT_NAME_VALUE
                , InputArgument::REQUIRED
                , 'the environment value'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force = true === $input->getOption(Environment::OPTION_NAME_FORCE);
        $name  = $input->getArgument(Environment::ARGUMENT_NAME_NAME);
        $value = $input->getArgument(Environment::ARGUMENT_NAME_VALUE);

        $oldOption    = $this->instanceDB->getOption($name);
        $optionExists = null !== $oldOption;

        if (false === $force) {
            if (true === $optionExists) {
                $this->writeError('option already exists', $output);
                return IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
            }
        }

        $this->instanceDB->addOption(
            $name
            , $value
        );

        $output->writeln(
            sprintf(
                "updated value for %s from %s to %s",
                $name
                , null !== $oldOption
                ? $oldOption
                : 'null'
                , $value
            )
        );
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
