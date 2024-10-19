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

final readonly class AdditionalData implements IJsonObject {

    public function __construct(
        private string            $id,
        private string            $key,
        private string            $value,
        private int               $nodeId,
        private DateTimeInterface $createTs
    ) {
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function getNodeId(): int {
        return $this->nodeId;
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'id'         => $this->getId()
            , 'key'      => $this->getKey()
            , 'nodeId'   => $this->getNodeId()
            , 'createTs' => $this->getCreateTs()
        ];
    }

}
