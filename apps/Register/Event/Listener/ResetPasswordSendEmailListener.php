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

namespace KSA\Register\Event\Listener;

use DateTimeImmutable;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\DTO\User\UserStateName;
use KSA\Register\Event\ResetPasswordEvent;
use KSA\Register\Exception\RegisterException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\L10N\IL10N;
use KSP\Core\Service\User\IUserStateService;
use Mezzio\Template\TemplateRendererInterface;
use Ramsey\Uuid\Uuid;

final readonly class ResetPasswordSendEmailListener implements IListener {

    public function __construct(
        private TemplateRendererInterface $templateRenderer
        , private IL10N                   $translator
        , private IEmailService           $emailService
        , private IConfigService          $configService
        , private IUserStateService       $userStateService
    ) {
    }

    #[\Override]
    public function execute(IEvent $event): void {

        if (false === ($event instanceof ResetPasswordEvent)) {
            throw new RegisterException();
        }

        $this->emailService->addRecipient(
            $event->getUser()->getName()
            , $event->getUser()->getEmail()
        );

        $this->emailService->setSubject(
            $this->translator->translate('Password Reset')
        );

        $hash = Uuid::uuid4()->toString();
        $this->emailService->setBody(
            $this->templateRenderer->render(
                'register::forgot_password', [
                    'hello'       => $this->translator->translate(
                        sprintf("Hey %s,", $event->getUser()->getName())
                    ),
                    'topic'       => $this->translator->translate("You requested to reset your Keestash password."),
                    'content'     => $this->translator->translate("If you have requested to reset your password, just click the link below. "),
                    'questions1'  => $this->translator->translate("In case of any questions,"),
                    'questions2'  => $this->translator->translate(" contact us here."),
                    'buttonText'  => $this->translator->translate("Reset Password"),
                    'thankYou'    => $this->translator->translate("Thank you,"),
                    'teamName'    => $this->translator->translate("The Keestash Team"),
                    'buttonUrl'   => $this->configService->getValue('frontend_url') . '/reset-password-confirm?reset-hash=' . $hash,
                    'currentYear' => (new DateTimeImmutable())->format('Y'),
                ]
            )
        );
        $this->emailService->send();

        $this->userStateService->setState(
            new UserState(
                0,
                $event->getUser(),
                UserStateName::REQUEST_PW_CHANGE,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );
    }

}
