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

namespace KSA\PasswordManager\Factory\Api\Node\Attachment;

use KSA\PasswordManager\Api\Node\Attachment\Get;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\Icon\IIconService;
use KSP\L10N\IL10N;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

class GetFactory {

    public function __invoke(ContainerInterface $container): Get {
        return new Get(
            $container->get(IL10N::class)
            , $container->get(IFileRepository::class)
            , $container->get(NodeRepository::class)
            , $container->get(FileRepository::class)
            , $container->get(IIconService::class)
            , $container->get(Config::class)
        );
    }

}