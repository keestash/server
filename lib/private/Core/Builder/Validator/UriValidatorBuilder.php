<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Builder\Validator;

use KSP\Core\Builder\Validator\IValidatorBuilder;
use Laminas\Validator\Uri as Validator;

class UriValidatorBuilder implements IValidatorBuilder {

    private bool $absolute = true;
    private bool $relative = true;

    public function withAbsolute(bool $absolute): UriValidatorBuilder {
        $instance           = clone $this;
        $instance->absolute = $absolute;
        return $instance;
    }

    public function withRelative(bool $relative): UriValidatorBuilder {
        $instance           = clone $this;
        $instance->relative = $relative;
        return $instance;
    }

    public function build(): Validator {
        return new Validator(
            [
                'allowRelative'   => $this->relative
                , 'allowAbsolute' => $this->absolute
            ]
        );
    }

}