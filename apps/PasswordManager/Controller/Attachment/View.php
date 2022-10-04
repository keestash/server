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


use Keestash\Exception\File\FileNotFoundException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSP\Core\Controller\AppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class View extends AppController {

    private FileRepository            $nodeFileRepository;
    private IFileRepository           $fileRepository;
    private IL10N                     $translator;
    private TemplateRendererInterface $templateRenderer;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , IL10N                   $l10n
        , FileRepository          $nodeFileRepository
        , IFileRepository         $fileRepository
        , IAppRenderer            $appRenderer
    ) {
        parent::__construct($appRenderer);

        $this->nodeFileRepository = $nodeFileRepository;
        $this->fileRepository     = $fileRepository;
        $this->translator         = $l10n;
        $this->templateRenderer   = $templateRenderer;
    }

    public function run(ServerRequestInterface $request): string {
        $fileId = $request->getAttribute("fileId");
        /** @var IUser $user */
        $user = $request->getAttribute(IUser::class);

        if (null === $fileId) {
            return $this->renderError(
                $this->translator->translate("No file found")
            );
        }

        try {
            $file = $this->fileRepository->get((int) $fileId);
        } catch (FileNotFoundException $exception) {
            return $this->renderError(
                $this->translator->translate("No file found (2)")
            );
        }


        try {
            $node = $this->nodeFileRepository->getNode($file);
        } catch (PasswordManagerException $exception) {
            return $this->renderError(
                $this->translator->translate("No file found (3)")
            );
        }

        if (
            $node->getUser()->getId() !== $user->getId()
            && false === $node->isSharedTo($user)
        ) {
            return $this->renderError(
                $this->translator->translate("No file found (4)")
            );
        }

        if (false === file_exists($file->getFullPath())) {
            return $this->renderError(
                $this->translator->translate("No file found (5)")
            );
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
        // TODO find a better way
        return '';
    }

    private function renderError(string $message): string {
        return $this->templateRenderer->render(
            'passwordManager::error'
            , [
                "message" => $message
            ]
        );
    }

}
