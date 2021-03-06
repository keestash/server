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

namespace KSA\GeneralApi\Factory\Command;

use KSA\GeneralApi\Command\QualityTool\ClearBundleJS;
use KSP\Core\ILogger\ILogger;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

class ClearBundleJSFactory {

    public function __invoke(ContainerInterface $container): ClearBundleJS {
        return new ClearBundleJS(
            $container->get(Config::class)
            , $container->get(ILogger::class)
        );
    }

}