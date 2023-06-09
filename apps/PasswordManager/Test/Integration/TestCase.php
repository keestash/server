<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Integration;

use DateTimeImmutable;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\DTO\User\IUser;

abstract class TestCase extends \KST\Integration\TestCase {

    protected function getRootFolder(IUser $user): Root {
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        return $nodeRepository->getRootForUser($user, 0, 0);
    }

    protected function createAndInsertCredential(
        string   $password
        , string $url
        , string $userName
        , string $title
        , IUser  $user
        , Folder $folder
    ): Edge {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        return $credentialService->insertCredential(
            $credentialService->createCredential(
                $password
                , $url
                , $userName
                , $title
                , $user
            )
            , $folder
        );
    }

    protected function createAndInsertFolder(
        IUser    $user
        , string $name
        , Folder $parentFolder
    ): Edge {
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        /** @var NodeService $nodeService */
        $nodeService = $this->getService(NodeService::class);

        $folder = new Folder();
        $folder->setUser($user);
        $folder->setName($name);
        $folder->setType(Node::FOLDER);
        $folder->setCreateTs(new DateTimeImmutable());

        $id = $nodeRepository->addFolder($folder);
        $folder->setId($id);

        $edge = $nodeService->prepareRegularEdge(
            $folder
            , $parentFolder
            , $user
        );

        return $nodeRepository->addEdge($edge);
    }

}