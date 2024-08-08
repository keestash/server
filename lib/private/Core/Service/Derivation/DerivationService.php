<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Core\Service\Derivation;

use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Core\Service\Derivation\IDerivationService;
use Laminas\Crypt\Key\Derivation\Scrypt;
use Psr\Log\LoggerInterface;

final readonly class DerivationService implements IDerivationService {

    public function __construct(
        private InstanceDB        $instanceDb
        , private LoggerInterface $logger
    ) {
    }

    public function derive(string $raw): string {
        return Scrypt::calc(
            $raw
            , (string) $this->instanceDb->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
            , 2048
            , 2
            , 1
            , 32
        );
    }

}