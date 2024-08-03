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
use Keestash\Core\DTO\User\UserStateName;
use Keestash\Exception\KeestashException;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\Register\Entity\Register\Event\Type;
use KSA\Register\Event\UserRegisteredEvent;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSA\Register\Exception\RegisterException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\IEventService;
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
        , private readonly IEventService             $eventService
    ) {
    }

    public function execute(IEvent $event): void {

        if (false === ($event instanceof UserRegisteredEvent)) {
            $this->logger->error('unknown event triggered', ['event' => $event]);
            throw new KeestashException();
        }

        if ($event->getType() === Type::REGULAR) {
            $this->logger->debug('start regular registration');
            $userState = $this->userStateRepository->getByUser(
                $event->getUser()
            );

            if ($userState->getState() !== UserStateName::LOCK) {
                $this->logger->warning('did not found user', ['user' => $event->getUser()]);
                return;
            }

            $this->emailService->setSubject(
                $this->translator->translate("Please confirm your Keestash account"),
            );

            $this->emailService->setBody(
                $this->templateRenderer->render(
                    'registerEmail::confirmation_mail', [
                        'hello'       => $this->translator->translate(
                            sprintf("Hey %s,", $event->getUser()->getName())
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
                sprintf("%s %s", $event->getUser()->getFirstName(), $event->getUser()->getLastName())
                , $event->getUser()->getEmail()
            );
            $sent = $this->emailService->send();
            $this->logger->info('send register email', ['sent' => $sent]);
            $mailLog = new MailLog();
            $mailLog->setId((string) Uuid::uuid4());
            $mailLog->setSubject(AfterRegistration::MAIL_LOG_TYPE_STARTER_EMAIL);
            $mailLog->setCreateTs(new DateTimeImmutable());
            $this->mailLogRepository->insert($mailLog);
            $this->logger->debug('end regular registration');
            return;
        }

        if ($event->getType() === Type::CLI) {
            $this->logger->debug('start cli registration');
            $this->eventService->execute(
                new UserRegistrationConfirmedEvent(
                    $event->getUser()
                    , 1
                )
            );
            $this->logger->debug('end cli registration');
            return;
        }

        if ($event->getType() === Type::SAAS) {
            $this->logger->debug('start saas registration');
            $this->logger->info(
                'nothing to do for saas, continuing with payment service',
            );
            $this->logger->debug('end saas registration');
            return;
        }
        $this->logger->debug('unknown register type', ['event' => $event, 'type' => $event->getType()->name]);
        throw new RegisterException('unknown type ' . $event->getType()->name);
    }

}
