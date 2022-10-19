<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KST\Integration\Core\Repository\AppRepository;

use DateTimeImmutable;
use Keestash\Core\DTO\App\Config\App;
use Keestash\Core\DTO\BackgroundJob\JobList;
use KSP\Core\DTO\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KST\TestCase;

class AppRepositoryTest extends TestCase {

    public function testReplace(): void {
        /** @var IAppRepository $appRepository */
        $appRepository = $this->getService(IAppRepository::class);

        $app = new App();
        $app->setId(AppRepositoryTest::class);
        $app->setCreateTs(new DateTimeImmutable());
        $app->setBackgroundJobs(new JobList());
        $app->setEnabled(true);
        $app->setVersion(1);
        $this->assertTrue(
            true === $appRepository->replace($app)
        );
    }

    public function testGetApp(): void {
        /** @var IAppRepository $appRepository */
        $appRepository = $this->getService(IAppRepository::class);

        $app = new App();
        $app->setId(AppRepositoryTest::class);
        $app->setCreateTs(new DateTimeImmutable());
        $app->setBackgroundJobs(new JobList());
        $app->setEnabled(true);
        $app->setVersion(1);
        $this->assertTrue(
            true === $appRepository->replace($app)
        );

        $retrievedApp = $appRepository->getApp(AppRepositoryTest::class);
        $this->assertTrue($retrievedApp instanceof IApp);
        $this->assertTrue($retrievedApp->getId() === $app->getId());
        $this->assertTrue(0 === ($retrievedApp->getCreateTs()->getTimestamp() - $app->getCreateTs()->getTimestamp()));
        $this->assertTrue($retrievedApp->getVersion() === $app->getVersion());
        $this->assertTrue($retrievedApp->isEnabled() === $app->isEnabled());
    }

    public function testGetAllApps(): void {
        /** @var IAppRepository $appRepository */
        $appRepository = $this->getService(IAppRepository::class);

        $app = new App();
        $app->setId(AppRepositoryTest::class);
        $app->setCreateTs(new DateTimeImmutable());
        $app->setBackgroundJobs(new JobList());
        $app->setEnabled(true);
        $app->setVersion(1);
        $this->assertTrue(
            true === $appRepository->replace($app)
        );
        $allApps = $appRepository->getAllApps();
        $this->assertTrue($allApps->size() === 1);
        $this->assertTrue($allApps->get(AppRepositoryTest::class) instanceof IApp);
        $this->assertTrue($allApps->get(AppRepositoryTest::class)->getId() === $app->getId());
    }

}