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

namespace KSA\InstallInstance\Command;

use Keestash;
use Keestash\Command\KeestashCommand;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Uninstall extends KeestashCommand {

    protected static $defaultName = "instance:uninstall";

    /** @var InstanceRepository */
    private $instanceRepository;

    public function __construct(InstanceRepository $instanceRepository) {
        parent::__construct(Uninstall::$defaultName);
        $this->instanceRepository = $instanceRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("unlinking instance db file");
        $this->removeInstanceDB($output);
        $output->writeln("dropping tables");
        $this->dropTables($output);
        $output->writeln("clearing config file");
        $this->clearConfig($output);
        return 0;
    }

    private function removeInstanceDB(OutputInterface $output): bool {
        $removed = @unlink(InstanceDB::getPath());

        if (true === $removed) {
            $this->writeInfo(
                "removed instance file"
                , $output
            );
        } else {
            $this->writeError(
                "could not remove instance file"
                , $output
            );
        }
        return $removed;
    }

    private function dropTables(OutputInterface $output, bool $includeSchema = false): bool {
        $dropped = $this->instanceRepository->dropSchema($includeSchema);
        if (true === $dropped) {
            $this->writeInfo(
                "dropped all tables"
                , $output
            );
        } else {
            $this->writeError(
                "could not drop tables"
                , $output
            );
        }
        return $dropped;
    }

    private function clearConfig(OutputInterface $output): bool {
        $overwritten = false !== file_put_contents(
                Keestash::getServer()->getConfigRoot() . "/config.php"
                , ""
            );

        if (true === $overwritten) {
            $this->writeInfo(
                "config file is cleared"
                , $output
            );
        } else {
            $this->writeError(
                "could not clear config file"
                , $output
            );
        }
        return $overwritten;
    }

}
