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

namespace Keestash\Command\Install;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Uninstall extends KeestashCommand {

    private InstanceRepository $instanceRepository;
    private Config             $config;

    public function __construct(
        InstanceRepository $instanceRepository
        , Config           $config
    ) {
        parent::__construct();
        $this->instanceRepository = $instanceRepository;
        $this->config             = $config;
    }

    protected function configure(): void {
        $this->setName("keestash:install:uninstall")
            ->setDescription("uninstalls the instance");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln("unlinking instance db file");
        $this->removeInstanceDB($output);
        $output->writeln("unlinking scss files");
        $this->removeCss($output);
        $output->writeln("unlinking js files");
        $this->removeJs($output);
        $output->writeln("dropping tables");
        $this->dropTables($output);
        $output->writeln("clearing config file");
        $this->clearConfig($output);
        return 0;
    }

    private function removeCss(OutputInterface $output): bool {
        $files        = glob(
            $this->config->get(ConfigProvider::INSTANCE_PATH) . '/public/css/*'
        );
        $removedCount = $this->removeFiles(
            $files
            , $output
        );

        return count($files) === $removedCount;
    }

    private function removeJs(OutputInterface $output): bool {
        $files        = glob(
            $this->config->get(ConfigProvider::INSTANCE_PATH) . '/public/js/*'
        );
        $removedCount = $this->removeFiles(
            $files
            , $output
        );

        return count($files) === $removedCount;
    }

    private function removeFiles(array $files, OutputInterface $output): int {
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                $removed = @unlink($file);

                if (false === $removed) {
                    $count++;
                    $this->writeInfo('removed ' . $file, $output);
                }
                continue;
            }
            $this->writeError($file . ' is not a file', $output);
        }
        return $count;
    }

    private function removeInstanceDB(OutputInterface $output): bool {
        $removed = @unlink($this->config->get(ConfigProvider::INSTANCE_DB_PATH));

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

    private function dropTables(OutputInterface $output): bool {
        $dropped = $this->instanceRepository->dropSchema();
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
                $this->config->get(ConfigProvider::CONFIG_PATH) . "/config.php"
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
