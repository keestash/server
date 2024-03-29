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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Attachment;

use KSA\PasswordManager\Api\Node\Attachment\Remove;
use KSA\PasswordManager\Test\Integration\TestCase;

/**
 * Class RemoveTest
 * @package KSA\PasswordManager\Test\Integration\Api\Node\Attachment
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * TODO test real remove, add a file, assign to a node, remove the file
 */
class RemoveTest extends TestCase {

    public function testWithNoId(): void {
        /** @var Remove $remove */
        $remove   = $this->getServiceManager()->get(Remove::class);
        $response = $remove->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNonExistent(): void {
        /** @var Remove $remove */
        $remove   = $this->getServiceManager()->get(Remove::class);
        $response = $remove->handle(
            $this->getVirtualRequest(['fileId' => 99999])
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

}