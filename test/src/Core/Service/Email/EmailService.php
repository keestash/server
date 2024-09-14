<?php
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

namespace KST\Service\Core\Service\Email;

use KSP\Core\Service\Email\IEmailService;
use Psr\Log\LoggerInterface;

final readonly class EmailService implements IEmailService {

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function addRecipient(string $name, string $email): void {
        // silence is golden
    }

    #[\Override]
    public function addCarbonCopy(string $name, string $email): void {
        // silence is golden
    }

    #[\Override]
    public function addBlindCarbonCopy(string $name, string $email): void {
        // silence is golden
    }

    #[\Override]
    public function addAttachment(string $path, string $name = ""): void {
        // silence is golden
    }

    #[\Override]
    public function setSubject(string $subject): void {
        // silence is golden
    }

    #[\Override]
    public function send(int $delay = 0): bool {
        $this->logger->debug('I should normally send a mail, but thanks to DI, I was overriden by a stub class and so I did not :)');
        return false;
    }

    #[\Override]
    public function setBody(string $body): void {
        // silence is golden
    }

    #[\Override]
    public function setAlternativeBody(string $body): void {
        // silence is golden
    }

}
