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

namespace KSA\PasswordManager\Entity\Node\Pwned;

use DateTimeInterface;
use KSA\PasswordManager\Entity\Node\Node;
use KSP\Core\DTO\Entity\IJsonObject;

class Passwords implements IJsonObject {

    private Node               $node;
    private int                $severity;
    private DateTimeInterface  $createTs;
    private ?DateTimeInterface $updateTs;

    public function __construct(
        Node                 $node
        , int                $severity
        , DateTimeInterface  $createTs
        , ?DateTimeInterface $updateTs
    ) {
        $this->node     = $node;
        $this->severity = $severity;
        $this->createTs = $createTs;
        $this->updateTs = $updateTs;
    }

    /**
     * @return Node
     */
    public function getNode(): Node {
        return $this->node;
    }

    /**
     * @return int
     */
    public function getSeverity(): int {
        return $this->severity;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdateTs(): ?DateTimeInterface {
        return $this->updateTs;
    }

    public function jsonSerialize(): array {
        return [
            'nodeId'     => $this->getNode()->getId()
            , 'severity' => $this->getSeverity()
            , 'createTs' => $this->getCreateTs()
            , 'updateTs' => $this->getUpdateTs()
        ];
    }

}