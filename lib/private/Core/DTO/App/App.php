<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace Keestash\Core\DTO\App;

use KSP\Core\DTO\App\IApp;

class App implements IApp {

    private string $id;
    private int    $order = 0;
    private string $name;
    private int    $version;

    #[\Override]
    public function getId(): string {
        return $this->id;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    #[\Override]
    public function getOrder(): int {
        return $this->order;
    }

    public function setOrder(int $order): void {
        $this->order = $order;
    }

    #[\Override]
    public function getName(): string {
        return (string) $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return int
     */
    #[\Override]
    public function getVersion(): int {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion(int $version): void {
        $this->version = $version;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'id'        => $this->getId()
            , 'order'   => $this->getOrder()
            , 'name'    => $this->getName()
            , 'version' => $this->getVersion()
        ];
    }


}
