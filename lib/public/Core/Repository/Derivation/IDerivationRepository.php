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

namespace KSP\Core\Repository\Derivation;

use Doctrine\DBAL\Exception;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Exception\Repository\Derivation\DerivationNotDeletedException;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\DTO\User\IUser;

interface IDerivationRepository {

    /**
     * @param IUser $user
     * @return void
     * @throws DerivationNotDeletedException
     * @throws Exception
     */
    public function clear(IUser $user): void;

    public function add(IDerivation $derivation): void;

    /**
     * @param IUser $user
     * @return IDerivation
     * @throws NoRowsFoundException
     * @throws PasswordManagerException
     */
    public function get(IUser $user): IDerivation;

    public function getAll(): ArrayList;

    public function remove(IDerivation $derivation): void;

}