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

namespace Keestash\Queue\Handler;

use _PHPStan_c24aa5a16\Nette\Neon\Exception;
use Keestash\Core\DTO\Queue\Result;
use Keestash\Core\Service\Email\EmailService;
use KSP\Core\DTO\Queue\IEmailMessage;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\Service\Email\IEmailService;
use KSP\Queue\Handler\IEmailHandler;

class EmailHandler implements IEmailHandler {

    /** @var IEmailService|EmailService */
    private IEmailService $emailService;

    public function __construct(IEmailService $emailService) {
        $this->emailService = $emailService;
    }

    public function handle(IMessage $message): IResult {

        // todo implement rate limiting
        // todo implement intelligent spam detection

        if (!$message instanceof IEmailMessage) {
            throw new Exception();
        }

        $payload = $message->getPayload();
        $this->emailService->addRecipient($payload['recipient']['name'], $payload['recipient']['email']);
        $this->emailService->setSubject($payload['subject']);
        $this->emailService->setBody($payload['content']);
        $sent = $this->emailService->send();

        $result = new Result();
        $result->setCode(
            true === $sent
                ? IResult::RETURN_CODE_OK
                : IResult::RETURN_CODE_NOT_OK
        );
        return $result;
    }

}