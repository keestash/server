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

namespace KSA\PasswordManager\Event\Listener;

use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Manager\DataManager\IDataManager;
use KSP\Core\Service\Event\Listener\IListener;

class PostUserDelete implements IListener {

    private CommentRepository $commentRepository;
    private NodeRepository    $nodeRepository;
    private IDataManager      $dataManager;

    public function __construct(
        CommentRepository $commentRepository
        , NodeRepository  $nodeRepository
        , IDataManager    $dataManager
    ) {
        $this->commentRepository = $commentRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->dataManager       = $dataManager;
    }

    public function execute(IEvent $event): void {
//        /** @var string $type */
//        $type = $parameters[0][0];
//        /** @var IUser $user */
//        $user = $parameters[0][1];
//        /** @var bool $removed */
//        $removed = $parameters[0][2];
//
//        if (false === $removed) return true;
//
//        $filesRemoved    = true;
//        $commentsRemoved = $this->commentRepository->removeForUser($user);
//        $nodeRemoved     = $this->nodeRepository->removeForUser($user);
//
//        /** @var FileList|null $fileList */
//        $fileList = RegistrationHookManager::queryCache("fileList");
//
//        if (null !== $fileList) {
//            $filesRemoved = $this->dataManager->removeAll($fileList);
//        }
//
//        return
//            true === $filesRemoved
//            && true === $commentsRemoved
//            && true === $nodeRemoved;

    }


}
