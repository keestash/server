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

namespace Keestash\Command\Configuration\ResponseCodes;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListAll extends KeestashCommand {

    public const string OPTION_NAME_CODE_OR_NAME = 'code-or-name';

    public function __construct(
        private readonly Config $config
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName('keestash:response-codes:list')
            ->setDescription('lists all response codes and their corresponding info')
            ->addOption(
                ListAll::OPTION_NAME_CODE_OR_NAME
                , 'r'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY
                , 'filters response codes'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {

        $codeOrName    = (array) $input->getOption(ListAll::OPTION_NAME_CODE_OR_NAME);
        $responseCodes = $this->config
            ->get(ConfigProvider::RESPONSE_CODES, new Config([]))
            ->toArray();

        if (0 === count($responseCodes)) {
            $this->writeInfo('no response codes found. Please check your options', $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $table = new Table($output);
        $table->setHeaders(['code', 'name']);

        foreach ($responseCodes as $name => $code) {
            if (false === $this->isFiltered($name, $code, $codeOrName)) {
                continue;
            }

            $table->addRow([$code, $name]);
        }

        $table->render();
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function isFiltered(string $name, int $code, array $codeOrName): bool {
        if (0 === count($codeOrName)) {
            return true;
        }

        foreach ($codeOrName as $cn) {
            if (true === str_contains($name, (string) $cn) || true === str_contains((string) $code, (string) $cn)) {
                return true;
            }
        }
        return false;
    }

}
