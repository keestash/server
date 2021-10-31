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
use KSA\PasswordManager\Api\Node\Organization\Add;
use KSA\PasswordManager\Api\Node\Organization\Remove;
use KSA\PasswordManager\Api\Node\Organization\Update;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Event\NodeAddedToOrganizationEvent;
use KSA\PasswordManager\Event\NodeOrganizationUpdatedEvent;
use KSA\PasswordManager\Event\NodeRemovedFromOrganizationEvent;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
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
    private EdgeService           $edgeService;

    public function __construct(
        NodeRepository          $nodeRepository
        , NodeEncryptionService $nodeEncryptionService
        , ILogger               $logger
        , EdgeService           $edgeService
    ) {
        $this->nodeRepository        = $nodeRepository;
        $this->nodeEncryptionService = $nodeEncryptionService;
        $this->logger                = $logger;
        $this->edgeService           = $edgeService;
    }

    /**
     * @param Event $event
     * @throws PasswordManagerException
     */
    public function execute(Event $event): void {

        if (
            false === $event instanceof NodeAddedToOrganizationEvent
            && false === $event instanceof NodeRemovedFromOrganizationEvent
            && false === $event instanceof NodeOrganizationUpdatedEvent
        ) {
            throw new PasswordManagerException();
        }
        $vector = new IntegerVector();
        $this->work(
            $event->getNode()
            , $vector
            , $event->getOrganization()
        );

        $this->handleEdges(
            get_class($event),
            null !== $event->getOrganization()
                ? $event->getOrganization()
                : $event->getNode()->getUser(),
            $event->getNode()
        );

    }

    private function work(
        Node             $node
        , IntegerVector  &$vector
        , ?IOrganization $organization = null
    ): void {

        if ($node instanceof Credential) {
            $this->recrypt(
                $node
                , $vector
                , $organization
            );
        } else if ($node instanceof Folder) {
            /** @var Edge $edge */
            foreach ($node->getEdges() as $edge) {
                $this->work(
                    $edge->getNode()
                    , $vector
                    , $organization
                );
            }
        }

    }

    private function recrypt(
        Credential       $credential
        , IntegerVector  &$vector
        , ?IOrganization $organization = null
    ): void {
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
        $credential->setOrganization(null); // unset the organization in order to encrypt with the new key
        $this->nodeEncryptionService->encryptNode($credential, $keyHolder);
        $this->nodeRepository->updateCredential($credential);

    }

    private function handleEdges(string $type, IKeyHolder $keyHolder, Node $node): void {

        if (false === $keyHolder instanceof IOrganization) {
            return;
        }

        if ($type === NodeAddedToOrganizationEvent::class) {
            $this->addEdges($keyHolder, $node);
        } else if ($type === NodeRemovedFromOrganizationEvent::class) {
            $this->removeEdges($node);
        } else if ($type === NodeOrganizationUpdatedEvent::class) {
            $this->removeEdges($node);
            $this->addEdges($keyHolder, $node);
        }

    }

    private function removeEdges(Node $node): void {
        $this->nodeRepository->removeEdgeByNodeId(
            $node->getId()
        );
    }

    private function addEdges(IKeyHolder $keyHolder, Node $node): void {

        /** @var IUser $user */
        foreach ($keyHolder->getUsers() as $user) {

            if ($user->getId() === $node->getUser()->getId()) {
                continue;
            }

            $this->nodeRepository->addEdge(
                $this->edgeService->prepareEdgeForOrganization(
                    $node
                    , $this->nodeRepository->getRootForUser($user)
                )
            );
        }

    }

}