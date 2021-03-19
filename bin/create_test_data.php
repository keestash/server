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

use Keestash\Core\Repository\User\UserRepository;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;

(function () {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../lib/versioncheck.php';
    require_once __DIR__ . '/../lib/filecheck.php';
    require_once __DIR__ . '/../lib/extensioncheck.php';
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../lib/Keestash.php';

    Keestash::init();
    $server = Keestash::getServer();
    /** @var NodeService $nodeService */
    $nodeService = $server->query(NodeService::class);
    /** @var NodeRepository $nodeRepository */
    $nodeRepository = $server->query(NodeRepository::class);
    /** @var UserRepository $userService */
    $userService = $server->query(UserRepository::class);
    $userId      = "2";
    $user        = $userService->getUserById($userId);

    if (null === $user) {
        echo 'no user';
        return;
    }

    $root = $nodeRepository->getRootForUser($user);
    $node = $root;
    while (null !== $node) {
        $removed = $nodeRepository->remove($node);

        if (false === $removed) {
            throw new Exception('could not remove ' . $node->getName());
        }

        /** @var Edge $edge */
        foreach ($root->getEdges() as $edge) {
            $node = $edge->getNode();
        }
    }

})();