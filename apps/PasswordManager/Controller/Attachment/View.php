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

namespace KSA\PasswordManager\Controller\Attachment;


use KSA\PasswordManager\Repository\Node\FileRepository;
use KSP\Core\Controller\AppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\File\IFileRepository;
use KSP\L10N\IL10N;

class View extends AppController {

    public const TEMPLATE_NAME_ERROR = "error.twig";

    private FileRepository  $nodeFileRepository;
    private IFileRepository $fileRepository;
    private IUser           $user;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , FileRepository $nodeFileRepository
        , IUser $user
        , IFileRepository $fileRepository
    ) {
        parent::__construct($templateManager, $l10n);

        $this->nodeFileRepository = $nodeFileRepository;
        $this->fileRepository     = $fileRepository;
        $this->user               = $user;
    }

    public function onCreate(): void {

    }

    public function create(): void {
        $fileId = $this->getParameter("fileId", null);

        if (null === $fileId) {
            $this->renderError(
                $this->getL10N()->translate("No file found")
            );
            return;
        }

        $file = $this->fileRepository->get((int) $fileId);

        if (null === $file) {
            $this->renderError(
                $this->getL10N()->translate("No file found (2)")
            );
            return;
        }

        $node = $this->nodeFileRepository->getNode($file);

        if (null === $node) {
            $this->renderError(
                $this->getL10N()->translate("No file found (3)")
            );
            return;
        }


        if (
            $node->getUser()->getId() !== $this->user->getId()
            && false === $node->isSharedToMe()
        ) {
            $this->renderError(
                $this->getL10N()->translate("No file found (4)")
            );
            return;
        }

        if (false === file_exists($file->getFullPath())) {
            $this->renderError(
                $this->getL10N()->translate("No file found (5)")
            );
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file->getMimeType());
        header('Content-Disposition: inline; filename="' . $file->getName() . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file->getSize());
        readfile($file->getFullPath());
    }

    private function renderError(string $message): void {
        $this->getTemplateManager()->replace(
            View::TEMPLATE_NAME_ERROR
            , [
                "message" => $message
            ]
        );
        $this->setAppContent(
            $this->getTemplateManager()->render(View::TEMPLATE_NAME_ERROR)
        );

    }

    public function afterCreate(): void {

    }

}
