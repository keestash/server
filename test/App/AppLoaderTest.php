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

namespace KST\App;

use Keestash\App\App;
use KST\TestCase;

class AppLoaderTest extends TestCase {

    public function testAppObject() {
        $app = new App();
        $app->setId("id");
        $app->setName("TestApp");
        $app->setBaseRoute("unit_test_route");
        $app->setFAIconClass("fa fa-unit-test");
        $app->setTemplatePath(__DIR__);
        $app->setNamespace(__NAMESPACE__);
        $app->setAppPath(__DIR__);
        $app->setOrder(1);

        $this->assertInstanceOf(App::class, $app);
        $this->assertTrue($app->getId() === "id");
        $this->assertTrue($app->getName() === "TestApp");
        $this->assertTrue($app->getBaseRoute() === "unit_test_route");
        $this->assertTrue($app->getFAIconClass() === "fa fa-unit-test");
        $this->assertTrue($app->getTemplatePath() === __DIR__);
        $this->assertTrue($app->getNamespace() === __NAMESPACE__);
        $this->assertTrue($app->getAppPath() === __DIR__);
        $this->assertTrue($app->getOrder() === 1);
    }

}
