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
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\File\FileService;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

require_once 'vendor/autoload.php';
/** @var ContainerInterface $container */
$container = require_once __DIR__ . '/lib/start.php';
/** @var InstanceDB $instanceDB */
$instanceDB = $container->get(InstanceDB::class);
/** @var Config $config */
$config = $container->get(Config::class);

$token = $_GET['token'] ?? null;

if (null === $token) {
    header("HTTP/1.0 404 Not Found");
    die();
}

$decoded = JWT::decode(
    $token
    , $instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
    , ['HS256']
);

$then = new DateTime();
$then->setTimestamp((int) $decoded->iat);
$then->modify('+3 minutes');

if ((new DateTime()) > $then) {
    header("HTTP/1.0 404 Not Found");
    die();
}

$file = $config->get(Keestash\ConfigProvider::IMAGE_PATH) . "/profile_image_" . $decoded->aud;
if (!is_file($file)) {
    $file = $config->get(Keestash\ConfigProvider::ASSET_PATH) . '/img/' . FileService::DEFAULT_PROFILE_PICTURE . ".png";
}
header('Content-Type:' . mime_content_type($file));
header('Content-Length: ' . filesize($file));
readfile($file);
