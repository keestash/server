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
use KSA\PasswordManager\Entity\Node\Node;

/**
 * Class Credential
 *
 * @package KSA\PasswordManager\Entity\Password
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Credential extends Node {

    private string     $username;
    private string     $password;
    private string     $url;
    private ?ArrayList $attachments  = null;
    private int        $credentialId = 0;
    private string     $entropy;

    #[\Override]
    public function getType(): string {
        return Node::CREDENTIAL;
    }

    #[\Override]
    public function getIcon(): string {
        return Node::ICON_KEY;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
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

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): void {
        $this->url = $url;
    }

    public function getCredentialId(): int {
        return $this->credentialId;
    }

    public function setCredentialId(int $credentialId): void {
        $this->credentialId = $credentialId;
    }

    public function setEntropy(string $entropy): void {
        $this->entropy = $entropy;
    }

    public function getEntropy(): string {
        return $this->entropy;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return parent::jsonSerialize() + [
                "username"        => base64_encode($this->getUsername())
                , "password"      => base64_encode($this->getPassword())
                , "url"           => base64_encode($this->getUrl())
                , "entropy"       => base64_encode($this->getEntropy())
                , "credential_id" => $this->getCredentialId()
                , "attachments"   => $this->getAttachments()
            ];
    }

}
