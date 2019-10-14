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

namespace KST\Api;

use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KST\KSTestCase;

class ApiTest extends KSTestCase {

    public function testApi() {
        $parameters      = [
            1
            , 2
            , 3
        ];
        $templateManager = parent::getServer()
            ->query(ITemplateManager::class);

        $this->assertTrue($templateManager instanceof ITemplateManager);
//
//        /** @var TestApi $testApi */
//        $testApi = parent::getSimpleApiRunner()->run(
//            TestApi::class
//            , parent::getServer()
//            , $parameters
//        );
//
//        $this->assertTrue($testApi->getParameters() === $parameters);

    }

}