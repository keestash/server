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
use Laminas\Validator\EmailAddress;

class EmailValidatorBuilderTest extends TestCase {

    private EmailAddress $emailAddress;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        /** @var EmailAddress $uriValidator */
        $this->emailAddress = $this->getServiceManager()->get(EmailAddress::class);
    }

    /**
     * @param string $email
     * @param bool   $valid
     * @return void
     * @dataProvider getRegularCaseData
     */
    public function testRegularCase(string $email, bool $valid): void {
        $this->assertTrue(
            $valid === $this->emailAddress->isValid($email)
        );
    }

    public static function getRegularCaseData(): array {
        return [
            ['dev@null.de', true]
            , ['http://aa.keestash.com', false]
        ];
    }

}