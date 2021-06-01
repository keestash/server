<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\PasswordManager\Command\Node\Folder;

use DateTime;
use Keestash\Command\KeestashCommand;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Repository\User\IUserRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateFolder extends KeestashCommand {

    private NodeRepository  $nodeRepository;
    private IUserRepository $userRepository;

    public function __construct(
        NodeRepository $nodeRepository
        , IUserRepository $userRepository
    ) {
        parent::__construct();

        $this->nodeRepository = $nodeRepository;
        $this->userRepository = $userRepository;
    }

    protected function configure() {
        $this->setName("password-manager:create-folder")
            ->setDescription("Creates a folder for a given user")
            ->addArgument("user_id", InputArgument::REQUIRED, "The user for whom the folder should be created")
            ->addArgument("name", InputArgument::REQUIRED, "The name for the folder")
            ->addArgument("parent", InputArgument::REQUIRED, "The parent node. Must be a folder or root (default)");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $userId = (string) $input->getArgument("user_id");
        $name   = $input->getArgument("name");
        $parent = (int) $input->getArgument("parent");

        $user = $this->userRepository->getUserById($userId);

        if (null === $user) {
            $this->writeError("No User found for $userId. Aborting!", $output);
            exit(1);
        }

        $parentNode = $this->nodeRepository->getNode($parent);

        if (null === $parentNode) {
            $this->writeError("No parent id found for $parent. Aborting!", $output);
            exit(1);
        }

        if ($parentNode->getUser()->getId() !== $user->getId()) {
            $this->writeError('parent does not belong to user', $output);
            exit(1);
        }

        $folder = new Folder();
        $folder->setName($name);
        $folder->setType(Node::FOLDER);
        $folder->setCreateTs(new DateTime());
        $folder->setUser($user);
        $folderId = $this->nodeRepository->addFolder($folder);

        if (null === $folderId) {
            $this->writeError("Could not create folder. Aborting!", $output);
            exit(1);
        }

        $folder->setId($folderId);

        $edge = new Edge();
        $edge->setCreateTs(new DateTime());
        $edge->setParent($parentNode);
        $edge->setNode($folder);
        $edge->setOwner($user);
        $edge->setType(Edge::TYPE_REGULAR);
        $edge = $this->nodeRepository->addEdge($edge);

        if (0 === $edge->getId()) {
            $this->writeError("Could not link node to parent. Aborting!", $output);
            $this->nodeRepository->remove($folder);
            exit(1);
        }
        return 1;
    }

}
