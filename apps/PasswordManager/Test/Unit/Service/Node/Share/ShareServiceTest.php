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

namespace KSA\PasswordManager\Test\Unit\Service\Node\Share;

use DateTime;
use DateTimeInterface;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KST\TestCase;

class ShareServiceTest extends TestCase {

    private ShareService      $shareService;
    private CredentialService $credentialService;

    protected function setUp(): void {
        parent::setUp();
        $this->shareService      = $this->getServiceManager()->get(ShareService::class);
        $this->credentialService = $this->getServiceManager()->get(CredentialService::class);
    }

    public function testDefaultExpireDate(): void {
        $defaultExpireDate  = $this->shareService->getDefaultExpireDate();
        $expectedExpireDate = new DateTime();
        $expectedExpireDate->modify('+3 days');

        $this->assertTrue($defaultExpireDate instanceof DateTimeInterface);
        $this->assertTrue($defaultExpireDate->format("Y.m.d") === $expectedExpireDate->format("Y.m.d"));
    }

    public function testGenerateSharingHash(): void {
        $credential = $this->credentialService->createCredential(
            "topsecret"
            , "myawsome.route"
            , "keestash.com"
            , "keestash"
            , $this->getUser()
            , new Folder()
        );
        $hash       = $this->shareService->generateSharingHash($credential);
        $this->assertTrue(true === is_string($hash));
    }

}