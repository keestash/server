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

namespace KSA\Activity\Test\Integration\Entity;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Activity\Entity\Activity;
use KSA\Activity\Entity\IActivity;
use KSA\Activity\Test\Integration\TestCase;
use KSP\Core\DTO\Entity\IJsonObject;

class ActivityTest extends TestCase {

    private Activity $activity;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->activity = new Activity(
            ''
            , ''
            , ''
            , new ArrayList()
            , new DateTimeImmutable()
        );
    }

    public function testEntityImplementsActivityInterface(): void {
        $this->assertInstanceOf(
            IActivity::class
            , $this->activity
        );
    }

    public function testImplementsJsonObject(): void {
        $this->assertInstanceOf(
            IJsonObject::class
            , $this->activity
        );
    }

}