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
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Encryption\Key\IKeyService;

class NodeEncryptionService {

    private EncryptionService $encryptionService;
    private IKeyService       $keyService;
    private ILogger           $logger;

    public function __construct(
        EncryptionService $encryptionService
        , IKeyService     $keyService
        , ILogger         $logger
    ) {
        $this->encryptionService = $encryptionService;
        $this->keyService        = $keyService;
        $this->logger            = $logger;
    }


    public function decryptNode(Node &$node, ?IKeyHolder $parentKeyHolder = null): void {

        if ($node instanceof Credential) {
            $this->decryptCredential($node, $parentKeyHolder);
            return;
        }

        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {

            $childNode = $edge->getNode();
            if ($childNode instanceof Folder) {
                $this->decryptNode($childNode, $childNode->getOrganization());
                continue;
            }
            $this->decryptCredential($childNode, $parentKeyHolder);
        }

    }

    private function decryptCredential(Credential &$credential, ?IKeyHolder $parentKeyHolder = null): void {

        $keyHolder = $credential->getUser();
        // 1. if credential has organization, set:
        if (null !== $credential->getOrganization()) {
            $keyHolder = $credential->getOrganization();
        } else if (null !== $parentKeyHolder) {
            $keyHolder = $parentKeyHolder;
        }

        $key = $this->keyService->getKey($keyHolder);

        $credential->getUsername()
            ->setPlain(
                $this->encryptionService->decrypt(
                    $key
                    , $credential->getUsername()->getEncrypted()
                )
            );

        $credential->getUrl()
            ->setPlain(
                $this->encryptionService->decrypt(
                    $key
                    , $credential->getUrl()->getEncrypted()
                )
            );

        $credential->getPassword()
            ->setPlain(
                $this->encryptionService->decrypt(
                    $key
                    , $credential->getPassword()->getEncrypted()
                )
            );

        $credential->getEntropy()
            ->setPlain(
                $this->encryptionService->decrypt(
                    $key
                    , (string) $credential->getEntropy()->getEncrypted()
                )
            );

    }

    public function encryptNode(Node &$node, ?IKeyHolder $parentKeyHolder = null): void {

        if ($node instanceof Credential) {
            $this->encryptCredential($node, $parentKeyHolder);
            return;
        }

        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {

            $childNode = $edge->getNode();
            if ($childNode instanceof Folder) {
                $this->encryptNode($childNode, $childNode->getOrganization());
                continue;
            }
            $this->encryptCredential($childNode, $parentKeyHolder);
        }

    }

    private function encryptCredential(Credential &$credential, ?IKeyHolder $parentKeyHolder = null): void {

        $keyHolder = $credential->getUser();
        // 1. if credential has organization, set:
        if (null !== $credential->getOrganization()) {
            $keyHolder = $credential->getOrganization();
        } else if (null !== $parentKeyHolder) {
            $keyHolder = $parentKeyHolder;
        }

        $key = $this->keyService->getKey($keyHolder);

        $this->logger->debug('recrypting ' . $credential->getId() . ' with key ' . $key->getId() . ' and keyholder ' . $key->getKeyHolder()->getId() . ' (' . get_class($key->getKeyHolder()) . ')');

        $credential->getUsername()
            ->setEncrypted(
                $this->encryptionService->encrypt(
                    $key
                    , $credential->getUsername()->getPlain()
                )
            );

        $credential->getUrl()
            ->setEncrypted(
                $this->encryptionService->encrypt(
                    $key
                    , $credential->getUrl()->getPlain()
                )
            );

        $credential->getEntropy()
            ->setEncrypted(
                $this->encryptionService->encrypt(
                    $key
                    , $credential->getEntropy()->getPlain()
                )
            );

        $passwordObject = $credential->getPassword();
        $passwordObject->setEncrypted(
            $this->encryptionService->encrypt(
                $key
                , (string) $passwordObject->getPlain()
            )
        );
        $credential->setPassword($passwordObject);
    }

}