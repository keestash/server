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

namespace KST\Unit\Core\Service\HTTP\Route;

use KSP\Core\Service\HTTP\Route\IRouteService;
use KST\TestCase;

class RouteServiceTest extends TestCase {

    private IRouteService $routeService;

    protected function setUp(): void {
        parent::setUp();
        $this->routeService = $this->getService(IRouteService::class);
    }

    /**
     * @return void
     * @dataProvider provideRouteToAppId
     */
    public function testRouteToAppId(string $route, string $appId): void {
        $result = $this->routeService->routeToAppId($route);
        $this->assertTrue($result === $appId);
    }

    public function provideRouteToAppId(): array {
        return [
            ['/password_manager', 'passwordmanager']
            , ['/install_instance', 'installinstance']
            , ['/install', 'install']
        ];
    }

}