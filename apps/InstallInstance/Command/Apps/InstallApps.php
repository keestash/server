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

namespace KSA\InstallInstance\Command\Apps;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\App\Config\App;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallApps extends KeestashCommand {

    public function __construct(
        private readonly Config           $config
        , private readonly IAppRepository $appRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:apps:install")
            ->setDescription("installs all apps");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        /** @var Config $appList */
        $appList       = $this->config->get(ConfigProvider::APP_LIST, new Config([]));
        $loadedApps    = $appList->toArray();
        $installedApps = $this->appRepository->getAllApps();

        foreach ($loadedApps as $id => $app) {
            $installedApp = $installedApps->get($id);

            if (null === $installedApp) {
                $appObject = new App();
                $appObject->setId($id);
                $appObject->setVersion($app[ConfigProvider::APP_VERSION]);
                $appObject->setEnabled(true);
                $appObject->setCreateTs(new \DateTimeImmutable());
                $this->appRepository->replace($appObject);
            }
            // TODO implement update
        }
        return 0;

    }

}