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

namespace KSA\Login\Test\Integration\Api;

use Keestash\Core\Repository\Instance\InstanceDB;
use KSA\Login\ConfigProvider;
use KSA\Login\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;

class ConfigurationTest extends TestCase {

    public function testGetConfiguration(): void {
        /** @var InstanceDB $instanceDb */
        $instanceDb = $this->getService(InstanceDB::class);
        $demo       = $instanceDb->getOption('demo');
        $instanceDb->addOption('demo', "true");
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , ConfigProvider::APP_CONFIGURATION
                    , []
                )
            );
        $data     = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertArrayHasKey('demo', $data);
        $this->assertNotNull($data['demo']);
        $this->assertArrayHasKey('demoMode', $data);
        $this->assertTrue($data['demoMode']);
        $this->assertArrayHasKey('registerEnabled', $data);
        $this->assertFalse($data['registerEnabled']);
        $this->assertArrayHasKey('forgotPasswordEnabled', $data);
        $this->assertFalse($data['forgotPasswordEnabled']);
        $instanceDb->addOption('demo', $demo ?? "false");
    }

    public function testGetConfigurationNonDemoMode(): void {
        /** @var InstanceDB $instanceDb */
        $instanceDb = $this->getService(InstanceDB::class);
        $demo       = $instanceDb->getOption('demo');
        $instanceDb->addOption('demo', "fasdfgsafaFAsda");
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , ConfigProvider::APP_CONFIGURATION
                    , []
                )
            );
        $data     = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertArrayHasKey('demo', $data);
        $this->assertNull($data['demo']);
        $this->assertArrayHasKey('demoMode', $data);
        $this->assertFalse($data['demoMode']);
        $this->assertArrayHasKey('registerEnabled', $data);
        $this->assertTrue($data['registerEnabled']);
        $this->assertArrayHasKey('forgotPasswordEnabled', $data);
        $this->assertTrue($data['forgotPasswordEnabled']);
        $instanceDb->addOption('demo', $demo ?? "false");
    }

}