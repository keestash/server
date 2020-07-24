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

namespace KSA\GeneralApi\Command\QualityTool;

use Keestash\Command\KeestashCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHPStan extends KeestashCommand {

    protected static $defaultName = "general-api:phpstan";

    /** @var string $instanceRoot */
    private $instanceRoot = null;

    public function __construct(string $instanceRoot) {
        parent::__construct(PHPStan::$defaultName);

        $this->instanceRoot = $instanceRoot;
    }

    protected function configure() {
        $this->setDescription("Runs PHPStan Quality Tool")
            ->setHelp("measures quality of all PHP source code files");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $command = "{$this->instanceRoot}vendor/bin/phpstan analyse -c {$this->instanceRoot}config/phpstan/phpstan.neon {$this->instanceRoot}apps {$this->instanceRoot}lib --level 5 --memory-limit=2G";
        $this->writeInfo($command, $output);
        $result = shell_exec($command);

        $this->writeComment($result, $output);
        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;

    }

}
