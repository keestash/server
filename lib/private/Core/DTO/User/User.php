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
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
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
    private string            $locale;
    private string            $language;
    private HashTable         $roles;
    private bool              $ldapUser;

    /**
     * @return string
     */
    #[\Override]
    public function getLocale(): string {
        return $this->locale;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getLanguage(): string {
        return $this->language;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void {
        $this->locale = $locale;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language): void {
        $this->language = $language;
    }

    /**
     * @return string|null
     */
    #[\Override]
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
    #[\Override]
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
     * @param object $object
     * @return bool
     */
    #[\Override]
    public function equals($object): bool {
        if ($object instanceof IUser) {
            return $this->getId() === $object->getId();
        }
        return false;
    }

    /**
     * @return int
     */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function getWebsite(): string {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website): void {
        $this->website = $website;
    }

    #[\Override]
    public function getHash(): string {
        return $this->hash;
    }

    public function setHash(string $hash): void {
        $this->hash = $hash;
    }

    #[\Override]
    public function isLocked(): bool {
        return $this->locked;
    }

    public function setLocked(bool $locked): void {
        $this->locked = $locked;
    }

    #[\Override]
    public function isDeleted(): bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void {
        $this->deleted = $deleted;
    }

    #[\Override]
    public function getRoles(): HashTable {
        return $this->roles;
    }

    public function setRoles(HashTable $roles): void {
        $this->roles = $roles;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function isLdapUser(): bool {
        return $this->ldapUser;
    }

    /**
     * @param bool $ldapUser
     */
    public function setLdapUser(bool $ldapUser): void {
        $this->ldapUser = $ldapUser;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\Override]
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
                , 'language'   => $this->getLanguage()
                , 'locale'     => $this->getLocale()
                , 'ldap_user'  => $this->isLdapUser()
            ];
    }

}
