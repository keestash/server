<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\GeneralApi\Command\Stylesheet;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\Service\Stylesheet\Compiler as StylesheetCompiler;
use KSP\Core\ILogger\ILogger;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Compiler
 *
 * @package KSA\GeneralApi\Command\Stylesheet
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Compiler extends KeestashCommand {

    private StylesheetCompiler $stylesheetCompiler;
    private Config             $config;
    private ILogger            $logger;

    public function __construct(
        StylesheetCompiler $compiler
        , Config $config
        , ILogger $logger
    ) {
        parent::__construct("general-api:compile-scss");

        $this->stylesheetCompiler = $compiler;
        $this->config             = $config;
        $this->logger             = $logger;
    }

    protected function configure(): void {
        $this->setDescription("Compiles all SCSS belonging to the instance and all apps")
            ->setHelp("Make sure your scss are located in the correct path");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {

        while (true) {
            $this->compileApps();
            $this->writeInfo("compiled all", $output);
            sleep(5);
        }
        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function compileApps(): void {

        $files = glob(
            $this->config->get(ConfigProvider::INSTANCE_PATH) . '/apps/*/scss/*.scss'
        );

        if (false === $files) {
            $this->logger->debug('nothing to log, files array is empty');
            return;
        }

        foreach ($files as $file) {

            $pathInfo    = pathinfo($file);
            $destination = realpath($this->config->get(ConfigProvider::INSTANCE_PATH) . '/public/css/') . '/' . $pathInfo['filename'] . '.css';

            $this->stylesheetCompiler->compile(
                $file
                , $destination
            );

        }

    }

}
