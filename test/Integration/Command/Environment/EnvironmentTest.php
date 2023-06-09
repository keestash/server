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

namespace KST\Integration\Command\Environment;

use Keestash\Core\Repository\Instance\InstanceDB;
use KSA\Instance\Test\Integration\TestCase;
use KSP\Command\IKeestashCommand;
use Ramsey\Uuid\Uuid;

class EnvironmentTest extends TestCase {

    public function testAdd(): void {
        $name  = Uuid::uuid4()->toString();
        $value = Uuid::uuid4()->toString();
        /** @var InstanceDB $instanceDb */
        $instanceDb = $this->getService(InstanceDB::class);
        $command    = $this->getCommandTester("keestash:environment:add");
        $command->execute(
            [
                'name'    => $name,
                'value'   => $value,
                '--force' => ''
            ]
        );
        $this->assertTrue($command->getStatusCode() === IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL);
        $this->assertTrue($instanceDb->getOption($name) === $value);
    }

}