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
use KSP\Core\DTO\Entity\IJsonObject;

class Breaches implements IJsonObject {

    private int                $nodeId;
    private ?array             $hibpData;
    private DateTimeInterface  $createTs;
    private ?DateTimeInterface $updateTs;

    public function __construct(
        int                  $nodeId
        , ?array             $hibpData
        , DateTimeInterface  $createTs
        , ?DateTimeInterface $updateTs
    ) {
        $this->nodeId   = $nodeId;
        $this->createTs = $createTs;
        $this->updateTs = $updateTs;
        $this->hibpData = $hibpData;
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

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdateTs(): ?DateTimeInterface {
        return $this->updateTs;
    }

    /**
     * @return array|null
     */
    public function getHibpData(): ?array {
        return $this->hibpData;
    }

    public function jsonSerialize(): array {
        return [
            'nodeId'      => $this->getNodeId()
            , 'hibp_data' => $this->getHibpData()
            , 'createTs'  => $this->getCreateTs()
            , 'updateTs'  => $this->getUpdateTs()
        ];
    }

}