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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Exception\KeestashException;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Verify extends KeestashCommand {

    public function __construct(
        private readonly Config $config
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('keestash:response-codes:verify')
            ->setDescription('verifies response codes are all valid and unique');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $responseCodes = $this->config
            ->get(ConfigProvider::RESPONSE_CODES, new Config([]))
            ->toArray();
        $nameTable     = new HashTable();
        $codeTable     = new HashTable();

        if (0 === count($responseCodes)) {
            $this->writeInfo('no response codes found. Please check your options', $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        foreach ($responseCodes as $name => $code) {

            if ($nameTable->containsKey($name)) {
                throw new KeestashException($name . ' already exists');
            }
            $nameTable->add($name, true);

            if ($codeTable->containsKey($code)) {
                throw new KeestashException($code . ' already exists');
            }
            $codeTable->add($code, true);

        }

        $this->writeInfo('everything fine here', $output);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}