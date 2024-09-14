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

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\KeestashCommand;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Command\IKeestashCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListEnvironment extends KeestashCommand {

    public const string OPTION_NAME_ALL = 'all';

    public function __construct(
        private readonly InstanceDB         $instanceDB
        , private readonly IDateTimeService $dateTimeService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("environment:list")
            ->setDescription("lists the app environment")
            ->setAliases(["keestash:environment:list"])
            ->addOption(
                ListEnvironment::OPTION_NAME_ALL
                , 'a'
                , InputOption::VALUE_NONE
                , 'whether sensitive information should be shown'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $verbose = $input->getOption(ListEnvironment::OPTION_NAME_ALL);

        $allOptions = (array) $this->instanceDB->getAll();
        $tableRows  = [];

        foreach ($allOptions as $option) {
            $name  = $option['name'];
            $value = $option['value'];

            if (
                false === $verbose
                && true === in_array(
                    $name
                    , [
                        InstanceDB::OPTION_NAME_INSTANCE_ID
                        , InstanceDB::OPTION_NAME_INSTANCE_HASH
                    ]
                    , true
                )
            ) {
                $value = '********************************';
            }
            $tableRows[] = [
                'id'          => $option['id']
                , 'name'      => $name
                , 'value'     => $value
                , 'create_ts' => $this->dateTimeService->
                fromFormat($option['create_ts'])
                    ->format(IDateTimeService::FORMAT_DMY_HIS)
            ];

        }
        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Value', 'Create Ts'])
            ->setRows($tableRows);
        $table->render();
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
