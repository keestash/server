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
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Contracts\EventDispatcher\Event;

class OrganizationAddListener implements IListener {

    private NodeRepository $nodeRepository;

    public function __construct(NodeRepository $nodeRepository) {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param NodeAddedToOrganizationEvent|NodeRemovedFromOrganizationEvent|Event $event
     */
    public function execute(Event $event): void {

        $node   = $this->nodeRepository->getNode(
            $event->getNode()->getId()
        );
        $vector = new IntegerVector();

        $this->work($node, $vector);

    }

    private function work(Node $node, IntegerVector &$vector): void {

        if ($node instanceof Credential) {
            $this->recrypt($node, $vector);
        } else if ($node instanceof Folder) {
            /** @var Edge $edge */
            foreach ($node->getEdges() as $edge) {
                if (true === $vector->get($node->getId())) continue;
                $vector->set($node->getId());
                $this->work($edge->getNode(), $vector);
            }
        }

    }

    private function recrypt(Credential $credential, IntegerVector &$vector): void {
        if (true === $vector->get($credential->getId())) return;
        $vector->set($credential->getId());
        $this->nodeRepository->updateCredential($credential);
    }

}