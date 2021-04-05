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

namespace KSA\InstallInstance\Controller;

use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Legacy\Legacy;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends FullscreenAppController {

    public const TEMPLATE_NAME_CONFIG_PART        = "config_part.twig";
    public const TEMPLATE_NAME_DIRS_WRITABLE_PART = "dirs_writable.twig";
    public const TEMPLATE_NAME_HAS_DATA_DIRS      = "has_data_dirs.twig";

    private InstallerService          $installerService;
    private ILogger                   $logger;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;
    private Legacy                    $legacy;

    public function __construct(
        IL10N $translator
        , InstallerService $installerService
        , ILogger $logger
        , IAppRenderer $appRenderer
        , TemplateRendererInterface $templateRenderer
        , Legacy $legacy
    ) {
        parent::__construct($appRenderer);

        $this->installerService = $installerService;
        $this->logger           = $logger;
        $this->templateRenderer = $templateRenderer;
        $this->translator       = $translator;
        $this->legacy           = $legacy;
    }

    public function run(ServerRequestInterface $request): string {

        if (true === $this->installerService->hasIdAndHash()) {
            // TODO we need to redirect, anyhow
        }

        return $this->templateRenderer
            ->render(
                'installInstance::install_instance'
                , [
                    "installationHeader"        => $this->translator->translate("Installation")
                    , "installInstruction"      => $this->translator->translate("Your {$this->legacy->getApplication()->get('name')} instance seems to be incomplete. Please follow the instructions below:")
                    , "endUpdate"               => $this->translator->translate("End Update")
                    , "configurationPartHeader" => $this->translator->translate("Configuration File")
                    , "dirsWritablePartHeader"  => $this->translator->translate("Files and Directories that are not Writable")
                    , "hasDataDirsPartHeader"   => $this->translator->translate("Data Directories that are missing")
                ]
            );

    }

}
