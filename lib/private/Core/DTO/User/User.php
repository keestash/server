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

namespace Keestash\Core\DTO\User;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Graph\Tree\BinarySearchTree;
use Exception;
use KSP\Core\DTO\User\IJsonUser;
use KSP\Core\Permission\IUser as PermissionUser;

class User implements IJsonUser, PermissionUser {

    /** @var int $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var string $password */
    private $password;
    /** @var DateTime $createTs */
    private $createTs;
    /** @var string $firstName */
    private $firstName;
    /** @var string $lastName */
    private $lastName;
    /** @var string $email */
    private $email;
    /** @var string $phone */
    private $phone;
    /** @var string $website */
    private $website;
    /** @var null|BinarySearchTree $roles */
    private $roles = null;
    /** @var null|DateTime */
    private $lastLogin = null;
    /** @var string $hash */
    private $hash = null;
    /** @var bool $locked */
    private $locked = false;
    /** @var bool $deleted */
    private $deleted = false;

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

    public function getLastLogin(): ?DateTime {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $lastLogin): void {
        $this->lastLogin = $lastLogin;
    }

    public function equals($object): bool {
        if ($object instanceof IJsonUser) {
            return $this->getId() === $object->getId();
        }
        return false;
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return
            [
                'id'           => $this->getId()
                , 'name'       => $this->getName()
                , 'create_ts'  => $this->getCreateTs()->getTimestamp()
                , 'first_name' => $this->getFirstName()
                , 'last_name'  => $this->getLastName()
                , 'email'      => $this->getEmail()
                , 'phone'      => $this->getPhone()
                , 'website'    => $this->getWebsite()
                , "roles"      => $this->getRoles()
                , "hash"       => $this->getHash()
                , "locked"     => $this->isLocked()
                , "deleted"    => $this->isDeleted()
            ];
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
    public function setName(?string $name): void {
        $this->name = $name;
    }

    /**
     * @return DateTime
     */
    public function getCreateTs(): DateTime {
        return $this->createTs;
    }

    /**
     * @param DateTime $createTs
     *
     * @throws Exception
     */
    public function setCreateTs(DateTime $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * @return string
     */
    public function getFirstName(): string {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getWebsite(): string {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website): void {
        $this->website = $website;
    }

    /**
     * returns the users roles
     *
     * @return BinarySearchTree|null
     */
    public function getRoles(): ?BinarySearchTree {
        return $this->roles;
    }

    /**
     * sets the users roles
     *
     * @param BinarySearchTree|null $roles
     */
    public function setRoles(?BinarySearchTree $roles): void {
        $this->roles = $roles;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function setHash(string $hash): void {
        $this->hash = $hash;
    }

    public function isLocked(): bool {
        return $this->locked;
    }

    public function setLocked(bool $locked): void {
        $this->locked = $locked;
    }

    public function isDeleted(): bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void {
        $this->deleted = $deleted;
    }

}
