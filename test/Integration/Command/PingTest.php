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

namespace KST\Integration\Command;

use KSA\Instance\Test\Integration\TestCase;
use KSP\Command\IKeestashCommand;

class PingTest extends TestCase {

    public function testPing(): void {
        $command = $this->getCommandTester('keestash:ping');
        $command->execute([]);
        $this->assertTrue($command->getStatusCode() === IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL);
    }

}