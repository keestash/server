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
use Laminas\Validator\EmailAddress as Validator;
use Laminas\Validator\Hostname;

class EmailValidatorBuilder implements IValidatorBuilder {

    private int  $allow          = Hostname::ALLOW_DNS;
    private bool $useDeepMxCheck = false;
    private bool $useDomainCheck = true;
    private bool $useMxCheck     = true;

    public function withAllow(int $val): EmailValidatorBuilder {
        $instance        = clone $this;
        $instance->allow = $val;
        return $instance;
    }

    public function withDeepMxCheck(bool $deepMxCheck): EmailValidatorBuilder {
        $instance                 = clone $this;
        $instance->useDeepMxCheck    = $deepMxCheck;
        return $instance;
    }

    public function withDomainCheck(bool $domainCheck): EmailValidatorBuilder {
        $instance                 = clone $this;
        $instance->useDomainCheck = $domainCheck;
        return $instance;
    }

    public function withUseMxCheck(bool $useMxCheck): EmailValidatorBuilder {
        $instance             = clone $this;
        $instance->useMxCheck = $useMxCheck;
        return $instance;
    }

    public function build(): Validator {
        return new Validator(
            [
                'allow'            => $this->allow
                , 'useDeepMxCheck' => $this->useDeepMxCheck
                , 'useDomainCheck' => $this->useDomainCheck
                , 'useMxCheck'     => $this->useMxCheck
            ]
        );
    }

}
