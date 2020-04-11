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

namespace Keestash\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class KeestashCommand extends Command {

    public const RETURN_CODE_NOT_RAN_SUCCESSFUL = 23456;
    public const RETURN_CODE_INVALID_ARGUMENT   = 12345;
    public const RETURN_CODE_RAN_SUCCESSFUL     = 0;

    protected function addRequiredArgument(string $name, string $description): void {
        $this->_addArgument($name, true, $description);
    }

    protected function addOptionalArgument(string $name, string $description): void {
        $this->_addArgument($name, false, $description);
    }

    private function _addArgument(string $name, bool $required, string $description): void {
        $this->addArgument($name, $required, $description);
    }

    protected function writeError(string $message, OutputInterface $output): void {
        $output->writeln('<error>' . $message . '</error>');
    }

    protected function writeInfo(string $message, OutputInterface $output): void {
        $output->writeln('<info>' . $message . '</info>');
    }

    protected function writeComment(string $message, OutputInterface $output): void {
        $output->writeln('<comment>' . $message . '</comment>');
    }

    protected function getArguments(string $name, InputInterface $input): array {
        $arguments = [];
        $args      = $input->getArgument($name);

        if (true === is_string($args)) {
            $arguments[] = $args;
        }

        if (true === is_array($args)) {
            $arguments = $args;
        }

        return $arguments;
    }

}
