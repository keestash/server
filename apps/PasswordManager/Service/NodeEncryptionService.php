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

use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\Repository\Derivation\DerivationException;
use Keestash\Exception\User\UserException;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\Service\Encryption\Key\IKeyService;
use Psr\Log\LoggerInterface;

class NodeEncryptionService {

    public function __construct(
        private readonly EncryptionService $encryptionService
        , private readonly IKeyService     $keyService
        , private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Node            $node
     * @param IKeyHolder|null $parentKeyHolder
     * @return void
     * @throws DerivationException
     * @throws EncryptionFailedException
     * @throws UserException
     */
    public function decryptNode(Node &$node, ?IKeyHolder $parentKeyHolder = null): void {
        $this->logger->debug(
            'start decrypt node'
            , [
                'nodeId'      => $node->getId()
                , 'keyHolder' => $parentKeyHolder?->getId()
            ]
        );
        if ($node instanceof Credential) {
            $this->logger->debug('node is credential, going to decrypt');
            $this->decryptCredential($node, $parentKeyHolder);
            return;
        }

        $this->logger->debug(
            'node is folder, going to recursively work'
            , [
                'type'       => $node::class
                , 'edgeSize' => $node->getEdges()->length()
            ]
        );

        /** @var Edge $edge */
        foreach ($node->getEdges() as $edge) {

            $childNode = $edge->getNode();
            if ($childNode instanceof Folder) {
                $this->logger->debug('childNode is a folder, going to recursive work');
                $this->decryptNode($childNode, $childNode->getOrganization());
                continue;
            }

            $this->logger->debug('node is credential, going to decrypt (2)');
            $this->decryptCredential($childNode, $parentKeyHolder);
        }

        $this->logger->debug(
            'end decrypt node'
            , [
                'nodeId'      => $node->getId()
                , 'keyHolder' => $parentKeyHolder?->getId()
            ]
        );
    }

    /**
     * @param Credential      $credential
     * @param IKeyHolder|null $parentKeyHolder
     * @return void
     * @throws EncryptionFailedException
     * @throws DerivationException
     * @throws UserException
     */
    private function decryptCredential(Credential &$credential, ?IKeyHolder $parentKeyHolder = null): void {
        $this->logger->debug(
            'start decryptCredential'
            , [
                'credentialId' => $credential->getId()
                , 'keyHolder'  => $parentKeyHolder?->getId()
            ]
        );
        $keyHolder = $credential->getUser();
        // 1. if credential has organization, set:
        if (null !== $credential->getOrganization()) {
            $this->logger->debug('keyholder is organization, using the organization key to decrypt', ['organizationId' => $credential->getOrganization()->getId()]);
            $keyHolder = $credential->getOrganization();
        } else if (null !== $parentKeyHolder) {
            $this->logger->debug('parentKeyholder is not null, using this one', ['parentKeyHolder' => $parentKeyHolder->getId(), 'type' => $parentKeyHolder::class]);
            $keyHolder = $parentKeyHolder;
        }

        $key = $this->keyService->getKey($keyHolder);
        $this->logger->debug(
            'retrieved key for keyholder'
            , [
                'keyHolderId' => $keyHolder->getId()
                , 'keyHolder' => $keyHolder::class
                , 'keyId'     => $key->getId()
            ]
        );

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
        $this->logger->debug('decryption done');

    }

    /**
     * @param Node            $node
     * @param IKeyHolder|null $parentKeyHolder
     * @return void
     */
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

    /**
     * @param Credential      $credential
     * @param IKeyHolder|null $parentKeyHolder
     * @return void
     */
    private function encryptCredential(Credential &$credential, ?IKeyHolder $parentKeyHolder = null): void {

        $keyHolder = $credential->getUser();
        // 1. if credential has organization, set:
        if (null !== $credential->getOrganization()) {
            $keyHolder = $credential->getOrganization();
        } else if (null !== $parentKeyHolder) {
            $keyHolder = $parentKeyHolder;
        }

        $key = $this->keyService->getKey($keyHolder);

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