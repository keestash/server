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

namespace KSA\InstallInstance\Command;

use Keestash\Command\KeestashCommand;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSA\InstallInstance\Entity\Environment as EnvironmentEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Environment extends KeestashCommand {

    public const OPTION_NAME_ENVIRONMENT = 'environment';

    public function __construct(
        private readonly InstanceDB $instanceDB
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:environment")
            ->setDescription("sets the app environment")
            ->addOption(
                Environment::OPTION_NAME_ENVIRONMENT
                , 'e'
                , InputOption::VALUE_REQUIRED
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $val         = $input->getOption(Environment::OPTION_NAME_ENVIRONMENT);
        $environment = EnvironmentEntity::from((string) $val);

        $this->instanceDB->addOption(
            InstanceDB::OPTION_NAME_ENVIRONMENT
            , $environment->value
        );

        $output->writeln(
            sprintf(
                "mode switched to %s",
                $environment->value
            )
        );
        return 0;
    }

}