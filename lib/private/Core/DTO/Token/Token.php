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

namespace Keestash\Core\DTO\Token;

use DateTimeInterface;
use doganoo\PHPUtil\Datatype\StringClass;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;

class Token implements IToken {

    private int               $id;
    private string            $value;
    private DateTimeInterface $timestamp;
    private string            $name;
    private IUser             $user;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getUser(): IUser {
        return $this->user;
    }

    public function setUser(IUser $user): void {
        $this->user = $user;
    }

    public function equals(IToken $token): bool {
        $string = new StringClass($token->getValue());
        return $string->equals($this->getValue());
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->timestamp = $createTs;
    }

    public function valid(): bool {
        return false === $this->expired();
    }

    public function expired(): bool {
//        return $this->getCreateTs()->getTimestamp() - 0; // TODO implement
        return false;
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->timestamp;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return [
            "id"          => $this->getId()
            , "value"     => $this->getValue()
            , "create_ts" => $this->getCreateTs()
            , "name"      => $this->getName()
            , "user"      => $this->getUser()
        ];
    }

}
