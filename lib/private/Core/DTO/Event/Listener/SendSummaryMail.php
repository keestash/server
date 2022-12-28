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
use Keestash\Core\DTO\Event\ApplicationStartedEvent;
use Keestash\Core\DTO\MailLog\MailLog;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class SendSummaryMail implements IListener {

    public const SUBJECT_SUMMARY_EMAIL = 'email.summary.subject';

    public function __construct(
        private readonly IMailLogRepository $mailLogRepository
        , private readonly IEmailService    $emailService
        , private readonly IUserRepository  $userRepository
        , private readonly LoggerInterface  $logger
    ) {
    }

    /**
     * @param IEvent|ApplicationStartedEvent $event
     * @return void
     */
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
        }

        $now  = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $referenceDate->getTimestamp();
        $this->logger->debug('summary', ['log' => $referenceDate->format('Y-m-d H:i:s'), 'diff' => $diff]);

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
            $body = 'no new users detected :(';
        }
        $this->emailService->setSubject(
            sprintf('Summary Mail [%s]', $now->format(IDateTimeService::FORMAT_DMY_HIS))
        );
        $this->emailService->setBody($body);
        $this->emailService->addRecipient('Dogan Ucar', 'dogan@dogan-ucar.de');
        $this->emailService->send();
        $mailLog = new MailLog();
        $mailLog->setId((string) Uuid::uuid4());
        $mailLog->setSubject(SendSummaryMail::SUBJECT_SUMMARY_EMAIL);
        $mailLog->setCreateTs(new DateTimeImmutable());
        $this->mailLogRepository->insert($mailLog);
    }

}