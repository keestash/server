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

namespace KSA\GeneralApi\Controller\File;

use Keestash\Core\Permission\PermissionFactory;
use KSP\Core\Controller\AppController;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\File\IFileRepository;
use KSP\L10N\IL10N;

/**
 * Class View
 * @package KSA\GeneralApi\Controller\File
 * @TODO    needs to be implemented for public files, such as icons or default images!
 */
class View extends AppController {

    private $parameters = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IFileManager $nodeFileManager
        , IFileRepository $fileRepository
    ) {
        parent::__construct($templateManager, $l10n);
    }

    public function onCreate(...$params): void {
        $this->parameters = $params;
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $fileId = $this->parameters["file_id"] ?? null;
    }

    public function afterCreate(): void {

    }

}