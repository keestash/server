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

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Keestash\ConfigProvider;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\File\FileService;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\File\Icon\IIconService;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

(static function (): void {

    require 'vendor/autoload.php';
    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/lib/start.php';
    /** @var InstanceDB $instanceDB */
    $instanceDB = $container->get(InstanceDB::class);
    /** @var Config $config */
    $config = $container->get(Config::class);
    /** @var IIconService $iconService */
    $iconService = $container->get(IIconService::class);
    /** @var IConfigService $configService */
    $configService = $container->get(IConfigService::class);
    /** @var LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);

    $lifeTime = $configService->getValue(
        'user_lifetime'
        , ConfigProvider::DEFAULT_USER_LIFETIME
    );

    $token = $_GET['token'] ?? null;

    if (null === $token || "" === $token) {
        header("HTTP/1.0 404 Not Found");
        $logger->info("no asset token given");
        die();
    }

    try {
        $decoded = JWT::decode(
            $token
            , new Key(
                $instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
                , 'HS256'
            )
        );
    } catch (Throwable $exception) {
        $logger->error("error while decoding token", ['exception' => $exception->getMessage(), 'token' => $token]);
        header("HTTP/1.0 404 Not Found");
        die();
    }

    $then = new DateTime();
    $then->setTimestamp((int) $decoded->iat + (int) $lifeTime);
    if ((new DateTime()) > $then) {
        $logger->info("outdated key", ['decoded' => $decoded]);
        header("HTTP/1.0 404 Not Found");
        die();
    }

    $audience = $decoded->aud;

    $file = null;
    if ($audience->type === IAudience::TYPE_USER) {

        $file       = $config->get(Keestash\ConfigProvider::IMAGE_PATH) . "/" . md5($audience->value) . "/profile_image_" . (int) $audience->value;
        $files      = glob($file . "*");
        $filesCount = count((array) $files);

        if (0 === $filesCount) {
            $file = $config->get(Keestash\ConfigProvider::ASSET_PATH) . '/img/' . FileService::DEFAULT_PROFILE_PICTURE . ".png";
        } else {
            $file = $files[0];
        }
    } else if ($audience->type === IAudience::TYPE_ASSET) {
        $fileName = $iconService->getIconForExtension($audience->value);
        $file     = $config->get(Keestash\ConfigProvider::ASSET_PATH) . '/svg/' . $fileName;
    }

    header('Content-Type:' . mime_content_type($file));
    header('Content-Length: ' . filesize($file));
    readfile($file);

})();
