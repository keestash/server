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

namespace KSA\GeneralApi\Api\Thumbnail;

use Keestash\Api\Response\ImageResponse;
use Keestash\ConfigProvider;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\File\Icon\IIconService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    private IIconService $iconService;
    private Config       $config;
    private ILogger      $logger;

    public function __construct(
        IIconService $iconService
        , Config $config
        , ILogger $logger
    ) {
        $this->iconService = $iconService;
        $this->config      = $config;
        $this->logger      = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $extension = $request->getAttribute('extension');

        if (null === $extension) {
            return new JsonResponse(['no thumbnail for ' . $extension . 'found']);
        }

        $assetDir = (string) $this->config->get(ConfigProvider::ASSET_PATH);
        $svgDir   = str_replace("//", "/", "$assetDir/svg/");
        $path     = $svgDir . $this->iconService->getIconForExtension($extension);

        $this->logger->debug($path);
        return new ImageResponse(
            $path
            , (string) mime_content_type($path)
        );
    }

}