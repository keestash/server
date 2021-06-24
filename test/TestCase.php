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

namespace KST;

use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase {

    private ServiceManager $serviceManager;

    protected function getServiceManager(): ServiceManager {
        return $this->serviceManager;
    }

    protected function setUp(): void {
        parent::setUp();
        $this->serviceManager = require __DIR__ . '/config/service_manager.php';
    }

    protected function getUser(): IUser {
        return $this->serviceManager->get(IUserRepository::class)
            ->getUserById((string) Config::TEST_USER_ID);
    }

}
