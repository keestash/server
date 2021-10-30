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

namespace KSA\PasswordManager\Event\Listener;

use doganoo\PHPAlgorithms\Datastructure\Vector\BitVector\IntegerVector;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Event\NodeAddedToOrganizationEvent;
use KSA\PasswordManager\Event\NodeRemovedFromOrganizationEvent;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class OrganizationChangeListener
 * @package KSA\PasswordManager\Event\Listener
 * @author  Dogan Ucar <dogan.ucar@check24.de>
 *          TODO this can be a kind of worker
 */
class OrganizationChangeListener implements IListener {

    private NodeRepository        $nodeRepository;
    private NodeEncryptionService $nodeEncryptionService;
    private ILogger               $logger;

    public function __construct(
        NodeRepository          $nodeRepository
        , NodeEncryptionService $nodeEncryptionService
        , ILogger               $logger
    ) {
        $this->nodeRepository        = $nodeRepository;
        $this->nodeEncryptionService = $nodeEncryptionService;
        $this->logger                = $logger;
    }

    /**
     * @param NodeAddedToOrganizationEvent|NodeRemovedFromOrganizationEvent|Event $event
     */
    public function execute(Event $event): void {

        $vector = new IntegerVector();
        $parent = $this->nodeRepository->getNode(
            $event->getNode()->getId()
        );
        $this->work($parent, $vector, $event->getOrganization());

    }

    private function work(Node $node, IntegerVector &$vector, ?IOrganization $organization = null): void {

        if ($node instanceof Credential) {
            $this->recrypt($node, $vector, $organization);
        } else if ($node instanceof Folder) {
            /** @var Edge $edge */
            foreach ($node->getEdges() as $edge) {
                $this->work($edge->getNode(), $vector, $organization);
            }
        }

    }

    private function recrypt(Credential $credential, IntegerVector &$vector, ?IOrganization $organization = null): void {
        if (true === $vector->get($credential->getId())) return;
        $vector->set($credential->getId());

        // 1. we need first to decrypt the data. Decryption is done by
        // the previous key. So we either need the previous organization
        // or the user to whom the credential belongs to
        $keyHolder = null !== $credential->getOrganization()
            ? $credential->getOrganization()
            : $credential->getUser();
        $this->nodeEncryptionService->decryptNode($credential, $keyHolder);

        // 2. as we want to encrypt with the new owner, we
        // need to check whether an organization is given.
        // The organization can be null if the organization
        // has been removed from the node.
        // If this is the case, encrpyt with the user again.
        $keyHolder = null !== $organization
            ? $organization
            : $credential->getUser();
        $this->nodeEncryptionService->encryptNode($credential, $keyHolder);
        $this->nodeRepository->updateCredential($credential);
    }

}