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

namespace KSA\PasswordManager\Test;

use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;

class TestCase extends \KST\TestCase {

    protected function getRootForUser(): Root {
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        return $nodeRepository->getRootForUser($user);
    }

    protected function createCredential(
        string $password
        , string $url
        , string $userName
        , string $title
    ): Edge {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        $user              = $this->getUser();
        $root              = $this->getRootForUser();
        $node              = $credentialService->createCredential(
            $password
            , $url
            , $userName
            , $title
            , $user
            , $root
        );
        return $credentialService->insertCredential($node, $root);
    }

    protected function getNode(int $id): Node {
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        return $nodeRepository->getNode($id, 0, 0);
    }

}