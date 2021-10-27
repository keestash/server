#!/usr/bin/env php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.If not, see <https://www.gnu.org/licenses/>.
 */

use doganoo\PHPAlgorithms\Datastructure\Stackqueue\Queue;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Service\User\UserService;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Container\ContainerInterface;

(function () {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../lib/versioncheck.php';
    require_once __DIR__ . '/../lib/filecheck.php';
    require_once __DIR__ . '/../lib/extensioncheck.php';
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/config.php';

    /** @var ContainerInterface $container */
    $container = require_once __DIR__ . '/../lib/start.php';

    /** @var NodeService $nodeService */
    $nodeService = $container->get(NodeService::class);
    /** @var NodeRepository $nodeRepository */
    $nodeRepository = $container->get(NodeRepository::class);
    /** @var UserRepository $userRepository */
    $userRepository = $container->get(UserRepository::class);
    /** @var UserService $userService */
    $userService = $container->get(UserService::class);
    /** @var IUserRepositoryService $userRepositoryService */
    $userRepositoryService = $container->get(IUserRepositoryService::class);
    /** @var CredentialService $credentialService */
    $credentialService = $container->get(CredentialService::class);

    $user = $userRepository->getUser("dogano");

    if (null === $user) {

        $user = new \Keestash\Core\DTO\User\User();
        $user->setCreateTs(new DateTime());
        $user->setWebsite("https://dogan-ucar.de");
        $user->setPhone("+15712345678");
        $user->setPassword(
                $userService->hashPassword("Dogancan1@")
        );
        $user->setLocked(false);
        $user->setHash(md5((string) time()));
        $user->setLastName("Ucar");
        $user->setFirstName("Dogan");
        $user->setEmail("dogan@dogan-ucar.de");
        $user->setDeleted(false);
        $user->setName("dogano");

        $user = $userRepositoryService->createUser($user);

    }

    $root  = $nodeRepository->getRootForUser($user);
    $queue = new Queue();
    $queue->enqueue($root);

    while ($queue->size() > 0) {
        /** @var Node $node */
        $node = $queue->dequeue();

        if ($node instanceof Root) {
            /** @var Edge $edge */
            foreach ($node->getEdges() as $edge) {
                $queue->enqueue($edge->getNode());
            }
            continue;
        }

        echo 'removing node... ' . $node->getName() . "\n";
        $nodeRepository->remove($node);

    }

    addNodesToParent(
            $root
            , $user
            , 0
            , 5
            , $nodeRepository
            , $nodeService
            , $credentialService
    );
    // 1 create some folder
    // 2 add some credential
    // 3 visit all folder and start at 1
    // repeat X times


})();

function addNodesToParent(
        Folder $parent
        , IUser $user
        , int $level
        , int $maxLevel
        , NodeRepository $nodeRepository
        , NodeService $nodeService
        , CredentialService $credentialService
) {
    if ($level > $maxLevel) return;

    $credentials = createCredential($credentialService, $user);
//    echo 'added ' . count($credentials) . ' to ' . $parent->getName() . "\n";
    /** @var Credential $credential */
    foreach ($credentials as $credential) {
        $credentialService->insertCredential($credential, $parent);
    }

    $folders = createFolder($user);
    /** @var Folder $folder */
    foreach ($folders as $folder) {
        $folderId = $nodeRepository->addFolder($folder);
        $folder->setId((int) $folderId);
        $nodeRepository->addEdge(
                $nodeService->prepareRegularEdge(
                        $folder
                        , $parent
                        , $user
                )
        );
        addNodesToParent(
                $folder
                , $user
                , $level + 1
                , $maxLevel
                , $nodeRepository
                , $nodeService
                , $credentialService
        );
    }

//    echo 'added ' . count($folders) . ' to ' . $parent->getName() . "\n";

}

function createCredential(
        CredentialService $credentialService
        , IUser $user
): array {
//    $credentialSize = rand(1, 5 );
    $credentialSize = 3;
    $credentials    = [];
    for ($i = 0; $i < $credentialSize; $i++) {
        $credential    = $credentialService->createCredential(
                md5((string) ($i . time()))
                , "https://dogan-ucar.de"
                , md5((string) ($i . time()))
                , md5((string) ($i . time()))
                , $user
                , md5((string) ($i . time()))
        );
        $credentials[] = $credential;
    }
    return $credentials;
}

function createFolder(IUser $user): array {
//    $folderSize = rand(1, 5);
    $folderSize = 3;
    $folders    = [];
    for ($i = 0; $i < $folderSize; $i++) {
        $nextFolder = new Folder();
        $nextFolder->setName(md5((string) ($i . time())));
        $nextFolder->setType(Node::FOLDER);
        $nextFolder->setUser($user);
        $nextFolder->setCreateTs(new DateTime());
        $folders[] = $nextFolder;
    }
    return $folders;
}