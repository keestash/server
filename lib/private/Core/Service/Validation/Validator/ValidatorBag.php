<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Service\Validation\Validator;

use KSP\Core\Service\Validation\Validator\IValidatorBag;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\ValidatorChain;

class ValidatorBag implements IValidatorBag {

    private $value;
    private $chain;

    public function __construct() {
        $this->chain = new ValidatorChain();
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value): void {
        $this->value = $value;
    }

    public function addValidator(AbstractValidator $validator): void {
        $this->chain->attach(
            $validator
            , false
            , 1
        );
    }

    public function getChain(): ValidatorChain {
        return $this->chain;
    }

}
