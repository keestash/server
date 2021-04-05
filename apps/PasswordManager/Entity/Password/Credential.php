<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> Dogan Ucar <dogan@dogan-ucar.de>
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

namespace KSA\PasswordManager\Entity\Password;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use JsonSerializable;
use KSA\PasswordManager\Entity\Node;
use Laminas\Validator\NotEmpty;

/**
 * Class Credential
 *
 * @package KSA\PasswordManager\Entity\Password
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Credential extends Node implements JsonSerializable {

    private string     $username     = "";
    private Password   $password;
    private ?ArrayList $attachments  = null;
    private ?string    $notes        = null;
    private ?string    $url          = null;
    private int        $credentialId = 0;

    public function getType(): string {
        return Node::CREDENTIAL;
    }

    public function getIcon(): string {
        return Node::ICON_KEY;
    }

    /**
     * @return string
     */
    public function getUsername(): string {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void {
        $this->username = $username;
    }

    /**
     * @return Password
     */
    public function getPassword(): Password {
        return $this->password;
    }

    /**
     * @param Password $password
     */
    public function setPassword(Password $password): void {
        $this->password = $password;
    }

    /**
     * @return null|ArrayList
     */
    public function getAttachments(): ?ArrayList {
        return $this->attachments;
    }

    /**
     * @param null|ArrayList $attachments
     */
    public function setAttachments(?ArrayList $attachments): void {
        $this->attachments = $attachments;
    }

    /**
     * @return null|string
     */
    public function getNotes(): ?string {
        return $this->notes;
    }

    /**
     * @param null|string $notes
     */
    public function setNotes(?string $notes): void {
        $this->notes = $notes;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string {
        return $this->url;
    }

    /**
     * @param null|string $url
     */
    public function setUrl(?string $url): void {
        $this->url = $url;
    }

    public function getCredentialId(): int {
        return $this->credentialId;
    }

    public function setCredentialId(int $credentialId): void {
        $this->credentialId = $credentialId;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return parent::jsonSerialize() + [
                "username"        => $this->getUsername()
                , "password"      => $this->getPassword()
                , "attachments"   => $this->getAttachments()
                , "notes"         => $this->getNotes()
                , "url"           => $this->getUrl()
                , "credential_id" => $this->getCredentialId()
            ];
    }

}
