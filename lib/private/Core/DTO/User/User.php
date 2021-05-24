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

use DateTimeInterface;
use KSP\Core\DTO\User\IUser;

/**
 * Class User
 *
 * @package Keestash\Core\DTO\User
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class User implements IUser {

    private int               $id;
    private string            $name;
    private string            $password;
    private DateTimeInterface $createTs;
    private string            $firstName;
    private string            $lastName;
    private string            $email;
    private string            $phone;
    private string            $website;
    private string            $hash;
    private bool              $locked  = false;
    private bool              $deleted = false;
    private ?string           $jwt     = null;

    /**
     * @return string|null
     */
    public function getJWT(): ?string {
        return $this->jwt;
    }

    /**
     * @param string|null $jwt
     */
    public function setJWT(?string $jwt): void {
        $this->jwt = $jwt;
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

    public function equals($object): bool {
        if ($object instanceof IUser) {
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
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return
            [
                'id'           => $this->getId()
                , 'name'       => $this->getName()
                , 'create_ts'  => $this->getCreateTs()
                , 'first_name' => $this->getFirstName()
                , 'last_name'  => $this->getLastName()
                , 'email'      => $this->getEmail()
                , 'phone'      => $this->getPhone()
                , 'website'    => $this->getWebsite()
                , "hash"       => $this->getHash()
                , "locked"     => $this->isLocked()
                , "deleted"    => $this->isDeleted()
                , 'jwt'        => $this->getJWT()
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
