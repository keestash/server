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

namespace KSA\PasswordManager\Entity\Node\Credential\AdditionalData;

use DateTimeInterface;
use KSP\Core\DTO\Entity\IJsonObject;

class AdditionalData implements IJsonObject {

    public function __construct(
        private readonly string            $id,
        private readonly string            $key,
        private readonly Value             $value,
        private readonly int               $nodeId,
        private readonly DateTimeInterface $createTs
    ) {
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * @return Value
     */
    public function getValue(): Value {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getNodeId(): int {
        return $this->nodeId;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    public function jsonSerialize(): array {
        return [
            'id'         => $this->getId()
            , 'key'      => $this->getKey()
            , 'nodeId'   => $this->getNodeId()
            , 'createTs' => $this->getCreateTs()
        ];
    }

}