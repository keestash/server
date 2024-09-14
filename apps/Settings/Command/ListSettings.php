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

namespace KSA\Settings\Command;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Command\KeestashCommand;
use KSA\Settings\Entity\Setting;
use KSA\Settings\Repository\SettingsRepository;
use KSP\Command\IKeestashCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListSettings extends KeestashCommand {

    public const ARGUMENT_NAME_KEY = 'key';

    public function __construct(private readonly SettingsRepository $settingsRepository) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("settings:list")
            ->setDescription("lists one or all settings")
            ->addArgument(
                ListSettings::ARGUMENT_NAME_KEY
                , InputArgument::OPTIONAL | InputArgument::IS_ARRAY
                , 'the settings id(s) or none to list all'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $settingKeys = (array) $input->getArgument(ListSettings::ARGUMENT_NAME_KEY);
        $settingList = new ArrayList();
        $tableRows = [];

        if ([] === $settingKeys) {
            $settingList = $this->settingsRepository->getAll();
        } else {
            foreach ($settingKeys as $key) {
                $settingList->add(
                    $this->settingsRepository->get((string) $key)
                );
            }
        }

        /** @var Setting $setting */
        foreach ($settingList as $setting) {
            $tableRows[] = [
                $setting->getKey()
                , $setting->getValue()
                , $setting->getCreateTs()->format(IDateTimeService::FORMAT_DMY_HIS)
            ];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Key', 'Value', 'Created'])
            ->setRows($tableRows);
        $table->render();

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}