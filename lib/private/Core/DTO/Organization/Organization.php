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

namespace Keestash\Core\DTO\Organization;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;

class Organization implements IOrganization {

    private int                $id;
    private string             $name;
    private string             $password;
    private DateTimeInterface  $createTs;
    private ?DateTimeInterface $activeTs = null;
    private ArrayList          $users;

    public function __construct() {
        $this->users = new ArrayList();
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @param DateTimeInterface $createTs
     */
    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * @return ?DateTimeInterface
     */
    public function getActiveTs(): ?DateTimeInterface {
        return $this->activeTs;
    }

    /**
     * @param ?DateTimeInterface $activeTs
     */
    public function setActiveTs(?DateTimeInterface $activeTs): void {
        $this->activeTs = $activeTs;
    }

    public function addUser(IUser $user): void {
        $this->users->add($user);
    }

    public function getUsers(): ArrayList {
        return $this->users;
    }

    public function setUsers(ArrayList $users): void {
        $this->users = $users;
    }

    /**
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function jsonSerialize(): array {
        return [
            'id'          => $this->getId()
            , 'name'      => $this->getName()
            , 'users'     => $this->getUsers()
            , 'create_ts' => $this->getCreateTs()
            , 'active_ts' => $this->getActiveTs()
        ];
    }

}