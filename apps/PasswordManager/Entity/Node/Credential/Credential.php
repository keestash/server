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

namespace KSA\PasswordManager\Entity\Node\Credential;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\PasswordManager\Entity\Node\Credential\Password\Entropy;
use KSA\PasswordManager\Entity\Node\Credential\Password\Password;
use KSA\PasswordManager\Entity\Node\Credential\Password\URL;
use KSA\PasswordManager\Entity\Node\Credential\Password\Username;
use KSA\PasswordManager\Entity\Node\Node;

/**
 * Class Credential
 *
 * @package KSA\PasswordManager\Entity\Password
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Credential extends Node {

    private Username   $username;
    private Password   $password;
    private URL        $url;
    private ?ArrayList $attachments  = null;
    private int        $credentialId = 0;
    private Entropy    $entropy;

    #[\Override]
    public function getType(): string {
        return Node::CREDENTIAL;
    }

    #[\Override]
    public function getIcon(): string {
        return Node::ICON_KEY;
    }

    /**
     * @return Username
     */
    public function getUsername(): Username {
        return $this->username;
    }

    /**
     * @param Username $username
     */
    public function setUsername(Username $username): void {
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
     * @return URL
     */
    public function getUrl(): URL {
        return $this->url;
    }

    /**
     * @param URL $url
     */
    public function setUrl(URL $url): void {
        $this->url = $url;
    }

    public function getCredentialId(): int {
        return $this->credentialId;
    }

    public function setCredentialId(int $credentialId): void {
        $this->credentialId = $credentialId;
    }

    public function isEncrypted(): bool {
        return
            true === $this->getUsername()->isEncrypted()
            && true === $this->getPassword()->isEncrypted()
            && true === $this->getUrl()->isEncrypted();
    }

    public function isDecrypted(): bool {
        return
            true === $this->getUsername()->isDecrypted()
            && true === $this->getPassword()->isDecrypted()
            && true === $this->getUrl()->isDecrypted();
    }

    /**
     * @param Entropy $entropy
     */
    public function setEntropy(Entropy $entropy): void {
        $this->entropy = $entropy;
    }

    /**
     * @return Entropy
     */
    public function getEntropy(): Entropy {
        return $this->entropy;
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
        return parent::jsonSerialize() + [
                "username"        => $this->getUsername()
                , "password"      => $this->getPassword()
                , "attachments"   => $this->getAttachments()
                , "url"           => $this->getUrl()
                , "credential_id" => $this->getCredentialId()
                , "entropy"       => $this->getEntropy()
            ];
    }

}
