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

use Keestash\ConfigProvider;
use KSA\GeneralApi\Api\Demo\AddEmailAddress;
use KSA\GeneralApi\Api\Thumbnail\Get;
use KSA\GeneralApi\Command\Info\Routes;
use KSA\GeneralApi\Command\Migration\MigrateApps;
use KSA\GeneralApi\Command\QualityTool\PHPStan;
use KSA\GeneralApi\Factory\Api\Demo\AddEmailAddressFactory;
use KSA\GeneralApi\Factory\Api\Thumbnail\GetFactory;
use KSA\GeneralApi\Factory\Command\Info\RoutesFactory;
use KSA\GeneralApi\Factory\Command\MigrateAppsFactory;
use KSA\GeneralApi\Factory\Command\PHPStanFactory;

return [
    ConfigProvider::FACTORIES => [
        // command
        MigrateApps::class     => MigrateAppsFactory::class,
        PHPStan::class         => PHPStanFactory::class,
        Routes::class          => RoutesFactory::class,

        // api
        AddEmailAddress::class => AddEmailAddressFactory::class,
        Get::class             => GetFactory::class,
    ],
];