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

namespace Keestash\Core\DTO\Event\Listener;

use DateTimeImmutable;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\MailLog\MailLog;
use Keestash\Exception\KeestashException;
use Keestash\Exception\Repository\NoRowsFoundException;
use Keestash\Exception\User\UserException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class SendSummaryMail implements IListener {

    public const SUBJECT_SUMMARY_EMAIL = 'email.summary.subject';

    public function __construct(
        private readonly IMailLogRepository          $mailLogRepository
        , private readonly IEmailService             $emailService
        , private readonly IUserRepository           $userRepository
        , private readonly LoggerInterface           $logger
        , private readonly IConfigService            $configService
        , private readonly TemplateRendererInterface $templateRenderer
        , private readonly IL10N                     $translator
    ) {
    }

    /**
     * @param IEvent $event
     * @return void
     * @throws KeestashException
     * @throws UserException
     */
    #[\Override]
    public function execute(IEvent $event): void {
        $referenceDate = new DateTimeImmutable();
        $referenceDate = $referenceDate->modify('-24 hour');
        $this->logger->debug('start summary mail');
        try {
            $mailLog       = $this->mailLogRepository->getLatestBySubject(
                SendSummaryMail::SUBJECT_SUMMARY_EMAIL
            );
            $referenceDate = $mailLog->getCreateTs();
        } catch (NoRowsFoundException $e) {
            $this->logger->debug('no rows found', ['exception' => $e, 'event' => $event]);
        }

        $now  = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $referenceDate->getTimestamp();
        $this->logger->debug(
            'summary',
            [
                'referenceDate' => $referenceDate->format('Y-m-d H:i:s'),
                'now'           => $now->format('Y-m-d H:i:s'),
                'diff'          => $diff,
                'willSent'      => $diff < 86400
            ]
        );

        if ($diff < 86400) {
            return;
        }

        $users = $this->userRepository->getAll();

        $body = '';
        /** @var IUser $user */
        foreach ($users as $user) {
            if ($user->getCreateTs() < $referenceDate) {
                continue;
            }
            $body = $body . sprintf(
                    "user %s created on %s"
                    , $user->getName()
                    , $user->getCreateTs()->format(IDateTimeService::FORMAT_DMY_HIS)
                );
        }

        if ($body === '') {
            $body = $this->translator->translate("Unfortunately there are no new users :(");
        }

        $this->emailService->setSubject(
            sprintf('Summary Mail [%s]', $now->format(IDateTimeService::FORMAT_DMY_HIS))
        );

        $this->emailService->setBody(
            $this->templateRenderer->render(
                'email::batch_mail', [
                    'hello'       => $this->translator->translate("Hello Admin,"),
                    'topic'       => $this->translator->translate("New users since yesterday"),
                    'content'     => $body,
                    'questions1'  => $this->translator->translate("In case of any questions,"),
                    'questions2'  => $this->translator->translate(" contact us here."),
                    'thankYou'    => $this->translator->translate("Thank you,"),
                    'teamName'    => $this->translator->translate("The Keestash Team"),
                    'currentYear' => (new DateTimeImmutable())->format('Y'),
                ]
            )
        );
        $this->emailService->addRecipient(
            (string) $this->configService->getValue("email_user")
            , (string) $this->configService->getValue("email_user")
        );
        $sent = $this->emailService->send();
        $this->logger->debug('send summary mail', ['sent' => $sent]);
        $mailLog = new MailLog();
        $mailLog->setId((string) Uuid::uuid4());
        $mailLog->setSubject(SendSummaryMail::SUBJECT_SUMMARY_EMAIL);
        $mailLog->setCreateTs(new DateTimeImmutable());
        $this->mailLogRepository->insert($mailLog);
    }

}