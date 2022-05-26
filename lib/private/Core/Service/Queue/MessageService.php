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

namespace Keestash\Core\Service\Queue;

use DateTime;
use Keestash\Core\DTO\Queue\EmailMessage;
use KSP\Core\DTO\Queue\IEmailMessage;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Queue\IMessageService;
use Ramsey\Uuid\Uuid;

class MessageService implements IMessageService {

    public function toEmailMessage(string $subject, string $content, IUser $recipient): IEmailMessage {
        $m = new EmailMessage();
        $m->setId((string) Uuid::uuid4());
        $m->setType(IMessage::TYPE_EMAIL);
        $m->setPayload(
            [
                'subject'     => $subject
                , 'content'   => $content
                , 'recipient' => [
                'name'    => $recipient->getFirstName() . ' ' . $recipient->getLastName()
                , 'email' => $recipient->getEmail()
            ]
            ]
        );
        $m->setReservedTs(new DateTime());
        $m->setAttempts(0);
        $m->setPriority(1);
        $m->setCreateTs(new DateTime());
        return $m;
    }

}