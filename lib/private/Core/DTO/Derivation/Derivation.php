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

namespace Keestash\Core\DTO\Derivation;

use DateTimeInterface;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;

class Derivation implements IDerivation {

    public function __construct(
        private readonly string              $id
        , private readonly IKeyHolder        $keyHolder
        , private readonly string            $derived
        , private readonly DateTimeInterface $createTs
    ) {
    }

    #[\Override]
    public function getId(): string {
        return $this->id;
    }

    #[\Override]
    public function getKeyHolder(): IKeyHolder {
        return $this->keyHolder;
    }

    #[\Override]
    public function getDerived(): string {
        return $this->derived;
    }

    #[\Override]
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

}