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

namespace Keestash\Core\Repository\Payment;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Exception\Payment\PaymentException;
use Keestash\Exception\Payment\PaymentNotCreatedException;
use KSP\Core\DTO\Payment\ILog;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Payment\IPaymentLogRepository;

class DefaultPaymentLogRepository implements IPaymentLogRepository {

    #[\Override]
    public function insert(ILog $log): void {
        throw new PaymentNotCreatedException();
    }

    #[\Override]
    public function update(ILog $log): ILog {
        throw new PaymentException();
    }

    #[\Override]
    public function get(string $key): ILog {
        throw new PaymentException();
    }

    #[\Override]
    public function getByUser(IUser $user): ILog {
        throw new PaymentException();
    }

    #[\Override]
    public function getAll(): ArrayList {
        throw new PaymentException();
    }

    #[\Override]
    public function getByType(Type $type): ILog {
        throw new PaymentException();
    }

}