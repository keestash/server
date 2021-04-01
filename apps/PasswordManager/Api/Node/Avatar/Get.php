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

namespace KSA\PasswordManager\Api\Node\Avatar;

use Keestash\Api\AbstractApi;
use Keestash\Api\Response\FileResponse;

use Keestash\Core\Service\File\FileService as CoreFileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository as NodeFileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

/**
 * Class Get
 * @package KSA\PasswordManager\Api\Node\Avatar
 */
class Get extends AbstractApi {

    private NodeFileRepository $nodeFileRepository;
    private NodeRepository     $nodeRepository;
    private RawFileService     $rawFileService;
    private CoreFileService    $coreFileService;

    public function __construct(
        IL10N $l10n
        , NodeFileRepository $nodeFileRepository
        , NodeRepository $nodeRepository
        , RawFileService $rawFileService
        , CoreFileService $coreFileService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeFileRepository = $nodeFileRepository;
        $this->nodeRepository     = $nodeRepository;
        $this->rawFileService     = $rawFileService;
        $this->coreFileService    = $coreFileService;
    }

    public function onCreate(array $parameters): void {

    }

    /**
     * @throws PasswordManagerException
     * @throws InvalidNodeTypeException
     */
    public function create(): void {
        $nodeId = $this->getParameter("nodeId");

        if (null === $nodeId) {
            throw new PasswordManagerException();
        }

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (null == $node) {
            throw new PasswordManagerException();
        }

        $files      = $this->nodeFileRepository->getFilesPerNode($node);
        $avatarFile = null;

        /** @var NodeFile $file */
        foreach ($files as $file) {
            if ($file->getType() === NodeFile::FILE_TYPE_AVATAR) {
                $avatarFile = $file;
                break;
            }
        }

        $file         = null === $avatarFile
            ? $this->coreFileService->getDefaultNodeAvatar()
            : $avatarFile->getFile();
        $fileResponse = new FileResponse(
            file_get_contents($file->getFullPath())
        );
        $fileResponse->addHeader('Content-Disposition', 'inline; filename="' . rawurldecode($file->getName()) . '"');
        $this->setResponse($fileResponse);

    }

    public function afterCreate(): void {

    }

}
