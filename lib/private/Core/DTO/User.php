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

namespace Keestash\Core\DTO;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Graph\Tree\BinarySearchTree;
use KSP\Core\DTO\IUser;
use KSP\Core\Permission\IUser as PermissionUser;

class User implements IUser, PermissionUser {

    /** @var int $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var string $password */
    private $password;
    /** @var \DateTime $createTs */
    private $createTs;
    /** @var string $displayName */
    private $displayName;
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

    /**
     * @return string
     * @deprecated
     */
    public function getDisplayName(): string {
        return $this->displayName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
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
    public function setName(?string $name): void {
        $this->name = $name;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTs(): \DateTime {
        return $this->createTs;
    }

    /**
     * @param int $createTs
     * @throws \Exception
     */
    public function setCreateTs(int $createTs): void {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($createTs);
        $this->createTs = $dateTime;
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
    public function geKSAstName(): string {
        return $this->lastName;
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

    public function setLastLogin(?DateTime $lastLogin): void {
        $this->lastLogin = $lastLogin;
    }

    public function getLastLogin(): ?DateTime {
        return $this->lastLogin;
    }

    public function setHash(string $hash): void {
        $this->hash = $hash;
    }

    public function getHash(): string {
        return $this->hash;
    }

    /**
     * Specify data which should be serialized to JSON
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
                , 'last_name'  => $this->geKSAstName()
                , 'email'      => $this->getEmail()
                , 'phone'      => $this->getPhone()
                , 'website'    => $this->getWebsite()
                , "roles"      => $this->getRoles()
                , "hash"       => $this->getHash()
            ];
    }

}