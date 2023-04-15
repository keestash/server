<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KST\Unit\Core\Service\App;

use Keestash\ConfigProvider;
use KSP\Core\DTO\App\IApp;
use KSP\Core\Service\App\IAppService;
use KST\Unit\TestCase;

class AppServiceTest extends TestCase {

    private IAppService $appService;

    protected function setUp(): void {
        parent::setUp();
        $this->appService = $this->getServiceManager()->get(IAppService::class);
    }

    public function testToApp(): void {
        $id   = "AppServiceTest";
        $name = $id;
        $app  = $this->appService->toApp(
            $id
            , [
                ConfigProvider::APP_NAME         => $name
                , ConfigProvider::APP_ORDER      => 1
                , ConfigProvider::APP_VERSION    => 1
                , ConfigProvider::APP_BASE_ROUTE => "index.php/my-awesome-route"
            ]
        );

        $this->assertInstanceOf(IApp::class, $app);
        $this->assertEquals($app->getId(), $id);
        $this->assertEquals($app->getName(), $name);
        $this->assertEquals($app->getOrder(), 1);
        $this->assertEquals($app->getVersion(), 1);
        $this->assertEquals($app->getBaseRoute(), "index.php/my-awesome-route"
        );
    }

    public function testToConfigApp(): void {
        $id   = "AppServiceTest";
        $name = $id;
        $app  = $this->appService->toApp(
            $id
            , [
                ConfigProvider::APP_NAME         => $name
                , ConfigProvider::APP_ORDER      => 1
                , ConfigProvider::APP_VERSION    => 1
                , ConfigProvider::APP_BASE_ROUTE => "index.php/my-awesome-route"
            ]
        );

        $configApp = $this->appService->toConfigApp($app);

        $this->assertInstanceOf(\KSP\Core\DTO\App\Config\IApp::class, $configApp);
        $this->assertEquals($configApp->getId(), $id);
        $this->assertEquals($configApp->isEnabled(), true);
        $this->assertEquals($configApp->getVersion(), 1);
    }

}