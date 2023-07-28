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

namespace Keestash\Command;

use DateTimeImmutable;
use doganoo\DI\DateTime\IDateTimeService;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Email\IEmailService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmail extends KeestashCommand {

    public function __construct(
        private readonly IEmailService      $emailService
        , private readonly IDateTimeService $dateTimeService
        , private readonly IConfigService   $configService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('keestash:test-email')
            ->setDescription('sends an test email to the administrator');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $timeStamp = $this->dateTimeService->toYMDHIS(new DateTimeImmutable());
        $this->emailService->setSubject(
            sprintf(
                'Keestash Test E-Mail %s'
                , $timeStamp
            )
        );

        $this->emailService->setBody(
            sprintf(
                'This is a test mail, generated on %s'
                , $timeStamp
            )
        );

        $this->emailService->addRecipient(
            (string) $this->configService->getValue("email_user")
            , (string) $this->configService->getValue("email_user")
        );
        $sent = $this->emailService->send();
        $this->writeInfo('email sent: ' . $sent, $output);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}