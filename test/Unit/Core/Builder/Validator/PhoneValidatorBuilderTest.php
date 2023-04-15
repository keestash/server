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

namespace KST\Unit\Core\Builder\Validator;

use KST\Unit\TestCase;
use Laminas\I18n\Validator\PhoneNumber;

class PhoneValidatorBuilderTest extends TestCase {

    private PhoneNumber $phoneNumberValidator;

    protected function setUp(): void {
        parent::setUp();
        /** @var PhoneNumber $uriValidator */
        $this->phoneNumberValidator = $this->getServiceManager()->get(PhoneNumber::class);
    }

    /**
     * @param string $phoneNumber
     * @param bool   $valid
     * @return void
     * @dataProvider getRegularCaseData
     */
    public function testRegularCase(string $phoneNumber, bool $valid): void {
        $this->assertTrue(
            $valid === $this->phoneNumberValidator->isValid($phoneNumber)
        );
    }

    public function getRegularCaseData(): array {
        return [
            ['', false]
            , ['http://aa.keestash.com', false]
            , ['-99999123456789', false]
        ];
    }

}