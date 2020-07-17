<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidatorService
 * @package Keestash\Core\Service\Validation
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class ValidatorService {

    private static $instance = null;

    public static function getInstance(): ValidatorInterface {

        if (null === ValidatorService::$instance) {
            ValidatorService::$instance = Validation::createValidatorBuilder()
                ->addMethodMapping('validate')
                ->getValidator();
        }
        return ValidatorService::$instance;
    }

    public function validate(IValidatable $validatable): ConstraintViolationListInterface {
        $validator = ValidatorService::getInstance();
        return $validator->validate($validatable);
    }

}