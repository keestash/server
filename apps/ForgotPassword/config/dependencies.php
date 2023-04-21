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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\ForgotPassword\Api\AccountDetails;
use KSA\ForgotPassword\Api\Configuration;
use KSA\ForgotPassword\Api\ForgotPassword;
use KSA\ForgotPassword\Api\ResetPassword;
use KSA\ForgotPassword\Event\Listener\ForgotPasswordMailLinkListener;
use KSA\ForgotPassword\Factory\Api\AccountDetailsFactory;
use KSA\ForgotPassword\Factory\Api\ConfigurationFactory;
use KSA\ForgotPassword\Factory\Api\ForgotPasswordFactory;
use KSA\ForgotPassword\Factory\Api\ResetPasswordFactory;
use KSA\ForgotPassword\Factory\Event\Listener\ForgotPasswordMailLinkListenerFactory;

return [
    CoreConfigProvider::FACTORIES => [
        // api
        ForgotPassword::class                   => ForgotPasswordFactory::class
        , ResetPassword::class                  => ResetPasswordFactory::class

        // event
        // -- listener
        , ForgotPasswordMailLinkListener::class => ForgotPasswordMailLinkListenerFactory::class
    ]
];