<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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
use Keestash\Core\DTO\MailLog\MailLog;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UserRegisteredEventListener implements IListener {

    public function __construct(
        private readonly IEmailService               $emailService
        , private readonly TemplateRendererInterface $templateRenderer
        , private readonly IL10N                     $translator
        , private readonly LoggerInterface           $logger
        , private readonly IMailLogRepository        $mailLogRepository
        , private readonly IUserStateRepository      $userStateRepository
    ) {
    }

    public function execute(IEvent $event): void {

        if (false === ($event instanceof UserRegistrationConfirmedEvent)) {
            return;
        }

        /** @var IUser $user */
        $user = $event->getUser();
        $this->emailService->setSubject(
            $this->translator->translate("Please confirm your Keestash account"),
        );

        $lockedUsers = $this->userStateRepository->getLockedUsers();
        $userState   = null;
        /** @var IUserState $us */
        foreach ($lockedUsers->toArray() as $us) {
            if ($us->getUser()->getId() === $user->getId()) {
                $userState = $us;
                break;
            }
        }

        if (null === $userState) {
            $this->logger->warning('did not found user', ['user' => $user]);
            return;
        }

        $this->emailService->setBody(
            $this->templateRenderer->render(
                'registerEmail::confirmation_mail', [
                    'hello'       => $this->translator->translate(
                        sprintf("Hey %s,", $user->getName())
                    ),
                    'topic'       => $this->translator->translate("Your Keestash account"),
                    'content'     => $this->translator->translate("Please confirm your registration."),
                    'questions1'  => $this->translator->translate("In case of any questions,"),
                    'questions2'  => $this->translator->translate(" contact us here."),
                    'buttonText'  => $this->translator->translate("Confirm"),
                    'thankYou'    => $this->translator->translate("Thank you,"),
                    'teamName'    => $this->translator->translate("The Keestash Team"),
                    'href'        => "https://app.keestash.com/confirmation?token=" . $userState->getStateHash(),
                    'currentYear' => (new DateTimeImmutable())->format('Y'),
                ]
            )
        );

        $this->emailService->addRecipient(
            sprintf("%s %s", $user->getFirstName(), $user->getLastName())
            , $user->getEmail()
        );
        $sent = $this->emailService->send();
        $this->logger->info('send register email', ['sent' => $sent]);
        $mailLog = new MailLog();
        $mailLog->setId((string) Uuid::uuid4());
        $mailLog->setSubject(AfterRegistration::MAIL_LOG_TYPE_STARTER_EMAIL);
        $mailLog->setCreateTs(new DateTimeImmutable());
        $this->mailLogRepository->insert($mailLog);
    }

}