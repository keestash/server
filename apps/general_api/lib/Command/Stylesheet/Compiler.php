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

use Keestash;
use Keestash\Command\KeestashCommand;
use Keestash\Core\Service\Stylesheet\Compiler as StylesheetCompiler;
use KSP\App\IApp;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Compiler
 *
 * @package KSA\GeneralApi\Command\Stylesheet
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Compiler extends KeestashCommand {

    protected static $defaultName = "stylesheet:compile-scss";

    /** @var StylesheetCompiler */
    private $stylesheetCompiler;

    /** @var string */
    private $scssRoot;

    public function __construct(
        StylesheetCompiler $compiler
        , string $scssRoot
    ) {
        parent::__construct(Compiler::$defaultName);

        $this->stylesheetCompiler = $compiler;
        $this->scssRoot           = $scssRoot;
    }

    protected function configure() {
        $this->setDescription("Compiles all SCSS belonging to the instance and all apps")
            ->setHelp("Make sure your scss are located in the correct path");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        while (true) {
            $this->compileCore();
            $this->compileApps();
            $this->writeInfo("compiled all", $output);
            sleep(5);
        }
        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function compileCore(): void {
        $this->stylesheetCompiler->compile(
            $this->scssRoot
            , $this->scssRoot . "dist/style.css"
        );
    }

    private function compileApps(): void {
        $apps = Keestash::getServer()
            ->getStylesheetManager()
            ->getApps();

        foreach ($apps->keySet() as $key) {

            /** @var IApp $app */
            $app = $apps->get($key);

            $this->stylesheetCompiler->compile(
                $app->getAppPath() . "/scss/"
                , $app->getAppPath() . "/scss/dist/style.css"
            );

        }

    }

}