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

namespace KST\Integration\Core\System\Instance;

use Keestash\Core\System\Installation\Instance\LockHandler;
use KST\Integration\TestCase;

class LockHandlerTest extends TestCase {

    public function testLockIsLockedUnlockAndDomain(): void {
        /** @var LockHandler $lockHandler */
        $lockHandler = $this->getService(LockHandler::class);
        $this->assertTrue(true === $lockHandler->lock());
        $this->assertTrue(true === $lockHandler->isLocked());
        $this->assertTrue(true === $lockHandler->unlock());
        $this->assertTrue(false === $lockHandler->isLocked());
        $this->assertTrue($lockHandler->getDomain() === LockHandler::DOMAIN_NAME . '.test');
    }

}