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

namespace KSP\Core\Service\Email;

use KSP\Core\Service\IService;

interface IEmailService extends IService {

    public function addRecipient(string $name, string $email): void;

    public function addCarbonCopy(string $name, string $email): void;

    public function addBlindCarbonCopy(string $name, string $email): void;

    public function addAttachment(string $path, string $name = ""): void;

    public function setSubject(string $subject): void;

    public function send(int $delay = 0): bool;

    public function setBody(string $body): void;

    public function setAlternativeBody(string $body): void;

}