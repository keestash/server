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

namespace KSA\ForgotPassword\Event\Listener;

use Keestash\Event\Worker\MessageProcessedEvent;
use KSA\ForgotPassword\ConfigProvider;
use KSA\ForgotPassword\Exception\ForgotPasswordException;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\Manager\EventManager\IEvent;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use Laminas\Config\Config;

class PasswordResetMailSendListener implements IListener {

    private Config               $config;
    private IUserStateRepository $userStateRepository;
    private IUserRepository      $userRepository;

    public function __construct(
        Config                 $config
        , IUserStateRepository $userStateRepository
        , IUserRepository      $userRepository
    ) {
        $this->config              = $config;
        $this->userStateRepository = $userStateRepository;
        $this->userRepository      = $userRepository;
    }

    public function execute(IEvent $event): void {
        if (!$event instanceof MessageProcessedEvent) {
            throw new ForgotPasswordException();
        }

        if (
            IResult::RETURN_CODE_OK === $event->getResult()->getCode()
            || true === (bool) $this->config->get("debug")
        ) {
            $stamp = $event->getMessage()->getStamp(ConfigProvider::STAMP_NAME_PASSWORD_RESET_MAIL_SENT);

            if (null === $stamp) {
                return;
            }

            $user = $this->userRepository->getUserByEmail(
                (string) $event->getMessage()->getPayload()['recipient']['email']
            );

            if (null === $user) {
                throw new ForgotPasswordException();
            }

            $this->userStateRepository->revertPasswordChangeRequest($user);
            $this->userStateRepository->requestPasswordReset($user, $stamp->getValue());
        }
    }

}