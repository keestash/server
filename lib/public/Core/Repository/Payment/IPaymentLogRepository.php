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

namespace KSP\Core\Repository\Payment;

use Doctrine\DBAL\Exception;
use JsonException;
use Keestash\Exception\Payment\PaymentException;
use KSP\Core\DTO\Payment\ILog;
use KSP\Core\DTO\User\IUser;

interface IPaymentLogRepository {

    public function insert(ILog $log): void;

    public function update(ILog $log): ILog;

    /**
     * @param string $key
     * @return ILog
     * @throws PaymentException
     * @throws Exception
     * @throws JsonException
     */
    public function get(string $key): ILog;

    /**
     * @param IUser $user
     * @return ILog
     * @throws Exception
     * @throws JsonException
     * @throws PaymentException
     */
    public function getByUser(IUser $user): ILog;

}