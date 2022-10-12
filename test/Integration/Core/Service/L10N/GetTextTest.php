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

namespace KST\Integration\Core\Service\L10N;

use KSP\Core\Service\L10N\IL10N;
use KST\TestCase;

class GetTextTest extends TestCase {

    private IL10N $translator;

    protected function setUp(): void {
        parent::setUp();
        $this->translator = $this->getService(IL10N::class);
    }

    public function testAll(): void {
        $this->assertTrue("de-DE" === $this->translator->getLanguageCode());
        $this->assertTrue("de-DE" === $this->translator->getLocaleCode());
        $this->assertTrue("blabla" === $this->translator->localize("blabla"));
        $this->assertTrue("blabla" === $this->translator->translate("blabla"));
    }

}