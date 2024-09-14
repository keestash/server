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

namespace KSA\PasswordManager\Entity\Node\Credential\Password;

use KSP\Core\DTO\Entity\IJsonObject;

abstract class Encryptable implements IJsonObject {

    private ?string $encrypted = null;
    private ?string $plain     = null;

    /**
     * @return string|null
     */
    public function getPlain(): ?string {
        return $this->plain;
    }

    /**
     * @param string|null $plain
     */
    public function setPlain(?string $plain): void {
        $this->plain = $plain;
    }

    public function getLength(): int {
        if (null === $this->getPlain()) return -1;
        return strlen($this->getPlain());
    }

    public function getEncrypted(): ?string {
        return $this->encrypted;
    }

    public function setEncrypted(string $encrypted): void {
        $this->encrypted = $encrypted;
    }

    public function getPlaceholder(): string {
        return str_pad('', 12, "*");
    }

    public function isEncrypted(): bool {
        return null !== $this->getEncrypted();
    }

    public function isDecrypted(): bool {
        return null !== $this->getPlain();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\Override]
    public function jsonSerialize(): array {
        return [
            "placeholder" => $this->getPlaceholder()
        ];
    }

}