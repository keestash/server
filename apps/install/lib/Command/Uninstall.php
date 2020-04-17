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

namespace KSA\Install\Command;

use Keestash\Command\KeestashCommand;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Installation\App\LockHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Uninstall extends KeestashCommand {

    protected static $defaultName = "apps:uninstall";

    /** @var InstanceRepository */
    private $instanceRepository;

    /** @var LockHandler */
    private $lockHandler;

    /** @var Migrator */
    private $migrator;

    /** @var UserService */
    private $userService;

    public function __construct(
        InstanceRepository $instanceRepository
        , LockHandler $lockHandler
        , Migrator $migrator
        , UserService $userService
    ) {
        parent::__construct(Uninstall::$defaultName);
        $this->instanceRepository = $instanceRepository;
        $this->lockHandler        = $lockHandler;
        $this->migrator           = $migrator;
        $this->userService        = $userService;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("locking in instanceDB");
        $this->lockInstallation($output);
        $output->writeln("dropping tables");
        $this->dropTables($output);
        $output->writeln("running core migrations");
        $this->runCoreMigrations($output);
        $output->writeln("running create system user");
        $this->createSystemUser($output);
        return 0;
    }

    private function lockInstallation(OutputInterface $output): bool {
        $locked = $this->lockHandler->lock();
        if (true === $locked) {
            $this->writeInfo(
                "locked installation"
                , $output
            );
        } else {
            $this->writeError(
                "could not lock installation"
                , $output
            );
        }
        return $locked;
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

    private function runCoreMigrations(OutputInterface $output): bool {
        $overwritten = $this->migrator->runCore();

        if (true === $overwritten) {
            $this->writeInfo(
                "core migrations ran"
                , $output
            );
        } else {
            $this->writeError(
                "could not run core migrations"
                , $output
            );
        }
        return $overwritten;
    }

    private function createSystemUser(OutputInterface $output): bool {
        $created = $this->userService->createSystemUser(
            $this->userService->getSystemUser()
        );

        if (true === $created) {
            $this->writeInfo(
                "system user created"
                , $output
            );
        } else {
            $this->writeError(
                "could not create system user"
                , $output
            );
        }
        return $created;
    }

}
