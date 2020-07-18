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

namespace Keestash\Core\Service\Validation;

use KSP\Core\DTO\Object\IValidatable;
use KSP\Core\Service\Validation\IValidationService;

class ValidationService implements IValidationService {

    /**
     * @param IValidatable $validatable
     *
     * @return string[]
     */
    public function validate(IValidatable $validatable): array {
        $validatorBags = $validatable->getValidators();
        $errors        = [];

        foreach ($validatorBags as $validatorBag) {

            $isValid = $validatorBag->getChain()->isValid(
                $validatorBag->getValue()
            );

            if (false === $isValid) {
                $errors = array_merge(
                    $errors
                    , $validatorBag->getChain()->getMessages()
                );
            }

        }
        return $errors;
    }

}
