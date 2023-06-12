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

namespace KSA\PasswordManager\Service\Node;

use DateTime;
use DateTimeImmutable;
use Keestash\Core\Service\User\UserService;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Event\Node\NodeRemovedEvent;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\Node\NodeNotRemovedException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Event\IEventService;

class NodeService {

    public function __construct(
        private readonly IUserRepository  $userRepository
        , private readonly NodeRepository $nodeRepository
        , private readonly UserService    $userService
        , private readonly IEventService  $eventService
    ) {
    }

    /**
     * @param int    $nodeId
     * @param string $userId
     *
     * @return bool
     *
     * TODO add more properties
     */
    public function isShareable(int $nodeId, string $userId): bool {
        try {
            $node = $this->nodeRepository->getNode($nodeId, 0, 1);
        } catch (PasswordManagerException $exception) {
            return false;
        }

        $user = $this->userRepository->getUserById($userId);

        return
            false === $this->userService->isDisabled($user)
            && false === $node->getUser()->equals($user)
            && false === $node->isSharedTo($user);
    }

    public function prepareSharedEdge(int $nodeId, string $userId): Edge {
        $expireTs = new DateTime();
        $expireTs->modify('+10 day');
        $edge = $this->prepareEdge($nodeId, $userId);
        $edge->setType(Edge::TYPE_SHARE);
        $edge->setExpireTs($expireTs);
        $edge->setCreateTs(new DateTimeImmutable());
        return $edge;
    }

    /**
     * TODO make security checks
     *
     * @param int    $nodeId
     * @param string $userId
     *
     * @return Edge
     */
    private function prepareEdge(int $nodeId, string $userId): Edge {
        $user       = $this->userRepository->getUserById($userId);
        $node       = $this->nodeRepository->getNode($nodeId);
        $parentNode = $this->nodeRepository->getRootForUser($user);

        $edge = new Edge();
        $edge->setNode($node);
        $edge->setParent($parentNode);

        return $edge;
    }

    public function prepareRegularEdge(Node $node, Node $parent, IUser $owner): Edge {
        $edge = new Edge();
        $edge->setNode($node);
        $edge->setParent($parent);
        $edge->setType(Edge::TYPE_REGULAR);
        $edge->setExpireTs(null);
        $edge->setOwner($owner);
        $edge->setCreateTs(new DateTime());
        return $edge;
    }

    public function createRootFolder(int $id, IUser $user): Root {
        $root = new Root();
        $root->setId($id);
        $root->setName(Node::ROOT);
        $root->setUser($user);
        $root->setCreateTs(new DateTime());
        $root->setUpdateTs(null);
        return $root;
    }

    public function isDeletable(string $type): bool {
        return
            true === $this->validType($type) &&
            $type !== Node::ROOT;
    }

    public function validType(string $type): bool {
        return in_array(
            $type
            , [
                Node::CREDENTIAL
                , Node::FOLDER
                , Node::ROOT
            ]
            , true
        );
    }

    public function getOrganization(Node $node): ?IOrganization {

        if (null !== $node->getOrganization()) {
            return $node->getOrganization();
        }

        $parents = $this->nodeRepository->getPathToRoot($node);
        foreach ($parents as $parent) {
            $nodeObject = $this->nodeRepository->getNode((int) $parent['id'], 0, 0);
            if (null !== $nodeObject->getOrganization()) return $nodeObject->getOrganization();
        }
        return null;
    }

    /**
     * @param Node $node
     * @return void
     * @throws InvalidNodeTypeException
     * @throws NodeNotRemovedException
     */
    public function removeNode(Node $node): void {
        $this->nodeRepository->remove($node);
        $this->eventService->execute(
            new NodeRemovedEvent($node)
        );
    }

    public function isValidNodeId(string $nodeId): bool {
        if (true === is_numeric($nodeId)) {
            return ((int) $nodeId) > 0;
        }
        return $nodeId === Node::ROOT;
    }

}
