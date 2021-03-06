<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Factory\Core\Service\Controller;

use Keestash\Core\Service\Controller\AppRenderer;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use Keestash\Legacy\Legacy;
use KSP\App\ILoader;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Router\IRouterService;
use KSP\L10N\IL10N;
use Laminas\Config\Config;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class AppRendererFactory {

    public function __invoke(ContainerInterface $container): IAppRenderer {
        return new AppRenderer(
            $container->get(IRouterService::class)
            , $container->get(Config::class)
            , $container->get(TemplateRendererInterface::class)
            , $container->get(Legacy::class)
            , $container->get(HTTPService::class)
            , $container->get(LockHandler::class)
            , $container->get(FileService::class)
            , $container->get(RawFileService::class)
            , $container->get(IFileManager::class)
            , $container->get(ILocaleService::class)
            , $container->get(ILoader::class)
            , $container->get(RouterInterface::class)
        );
    }

}