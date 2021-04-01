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

use KSA\PasswordManager\Repository\Node\FileRepository;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Core\Repository\File\IFileRepository;
use Symfony\Contracts\EventDispatcher\Event;

class PreUserDelete implements IListener {

    /** @var FileRepository */
    private $fileRepository;

    /** @var IFileRepository */
    private $coreFileRepository;

    public function __construct(
        FileRepository $fileRepository
        , IFileRepository $coreFileRepository
    ) {
        $this->fileRepository     = $fileRepository;
        $this->coreFileRepository = $coreFileRepository;
    }

    public function execute(Event $event): void {
//        /** @var IUser $user */
//        $type = $parameters[0][0];
//        $user = $parameters[0][1];
//
//        if ($type === UserStateHookManager::HOOK_TYPE_LOCK) return true;
//
//        $fileList = $this->coreFileRepository->getAll(
//            $this->fileRepository->getFilesByUser($user)
//        );
//
//        UserStateHookManager::cache("fileList", $fileList);
//        return true;
    }

}
