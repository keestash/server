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

namespace KSA\PasswordManager\Service;

use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\Service\Encryption\Key\IKeyService;

class NodeEncryptionService {

    private EncryptionService $encryptionService;
    private IKeyService       $keyService;

    public function __construct(
        EncryptionService $encryptionService
        , IKeyService     $keyService
    ) {
        $this->encryptionService = $encryptionService;
        $this->keyService        = $keyService;
    }

    public function decryptNode(Node &$node): void {
        $this->decryptNodeHelper($node);
    }

    public function encryptNode(Node &$node): void {
        $this->encryptNodeHelper($node);
    }

    private function decryptNodeHelper(Node &$node, ?IKeyHolder $keyHolder = null): void {

        $keyHolder = $this->getKeyHolder($node, $keyHolder);
        if ($node instanceof Credential) {
            $this->decryptCredential($node, $keyHolder);
            return;
        }

        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {

            $childNode = $edge->getNode();
            if ($childNode instanceof Folder) {
                $this->decryptNodeHelper($childNode, $keyHolder);
            }
            $this->decryptCredential($childNode, $keyHolder);
        }

    }

    private function encryptNodeHelper(Node &$node, ?IKeyHolder $keyHolder = null): void {

        $keyHolder = $this->getKeyHolder($node, $keyHolder);
        if ($node instanceof Credential) {
            $this->encryptCredential($node, $keyHolder);
            return;
        }

        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {

            $childNode = $edge->getNode();
            if ($childNode instanceof Folder) {
                $this->encryptNodeHelper($childNode, $keyHolder);
            }
            $this->encryptCredential($childNode, $keyHolder);
        }

    }

    private function decryptCredential(Credential &$credential, IKeyHolder $keyHolder): void {

        $key = $this->keyService->getKey($keyHolder);

        $credential->setUsername(
            $this->encryptionService->decrypt(
                $key
                , $credential->getUsername()
            )
        );

        $credential->setUrl(
            $this->encryptionService->decrypt(
                $key
                , $credential->getUrl()
            )
        );

        $credential->setNotes(
            $this->encryptionService->decrypt(
                $key
                , $credential->getNotes()
            )
        );

    }

    private function encryptCredential(Credential &$credential, IKeyHolder $keyHolder): void {

        $key = $this->keyService->getKey($keyHolder);

        $credential->setUsername(
            $this->encryptionService->encrypt(
                $key
                , $credential->getUsername()
            )
        );

        $credential->setUrl(
            $this->encryptionService->encrypt(
                $key
                , $credential->getUrl()
            )
        );

        $credential->setNotes(
            $this->encryptionService->encrypt(
                $key
                , $credential->getNotes()
            )
        );

        if (null === $credential->getPassword()->getPlain()) {
            return;
        }

        $passwordObject = $credential->getPassword();
        $passwordObject->setEncrypted(
            $this->encryptionService->encrypt(
                $key
                , $passwordObject->getPlain()
            )
        );
        $credential->setPassword($passwordObject);
    }

    private function getKeyHolder(Node $node, ?IKeyHolder $keyHolder): IKeyHolder {

        // 1. key is null
        // 2. keyholder is organization

//        if ($keyHolder === null || null !== $node->getOrganization()) {

            return null !== $node->getOrganization()
                ? $node->getOrganization()
                : $node->getUser();

//        }

//        return $keyHolder;
    }

}