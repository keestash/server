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

use DateTime;
use KSP\Command\IKeestashCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class KeestashCommand extends Command implements IKeestashCommand {

    protected function addRequiredArgument(string $name, string $description): void {
        $this->_addArgument($name, InputArgument::REQUIRED, $description);
    }

    private function _addArgument(string $name, ?int $required, string $description): void {
        $this->addArgument($name, $required, $description);
    }

    protected function addOptionalArgument(string $name, string $description): void {
        $this->_addArgument($name, InputArgument::OPTIONAL, $description);
    }

    protected function writeError(string $message, OutputInterface $output): void {
        $this->write($message, "error", $output);
    }

    private function write(string $message, string $tag, OutputInterface $output): void {
        $dateTime = $this->getFormattedDateTime();
        $output->writeln('<' . $tag . '>' . $dateTime . ": " . $message . '<' . $tag . '>');
    }

    private function getFormattedDateTime(): string {
        $dateTime = new DateTime();
        return $dateTime->format("Y-m-d H:i:s");
    }

    protected function writeInfo(string $message, OutputInterface $output): void {
        $this->write($message, "info", $output);
    }

    protected function writeComment(string $message, OutputInterface $output): void {
        $this->write($message, "comment", $output);
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

    /**
     * @param string          $question
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $default
     * @return bool
     */
    protected function askQuestion(
        string            $question
        , InputInterface  $input
        , OutputInterface $output
        , bool            $default = false
    ): bool {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion($question, $default);
        return $helper->ask($input, $output, $question);
    }

}
