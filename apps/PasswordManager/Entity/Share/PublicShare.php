<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace KSA\PasswordManager\Entity\Share;

use DateTimeInterface;
use KSP\Core\DTO\Entity\IJsonObject;

readonly class PublicShare implements IJsonObject {

    public function __construct(
        private int               $id,
        private int               $nodeId,
        private string            $hash,
        private DateTimeInterface $expireTs,
        private string            $password,
        private string            $secret
    ) {
    }

    public function getId(): int {
        return $this->id;
    }

    public function getNodeId(): int {
        return $this->nodeId;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function getExpireTs(): DateTimeInterface {
        return $this->expireTs;
    }

    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getSecret(): string {
        return $this->secret;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            "id"          => $this->getId()
            , "hash"      => $this->getHash()
            , "expire_ts" => $this->getExpireTs()
            , "node_id"   => $this->getNodeId()
        ];
    }

}
