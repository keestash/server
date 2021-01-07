<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace Keestash\Core\Service\Email;

use Exception;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Legacy\Legacy;
use KSP\Core\ILogger\ILogger;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService {

    public const HAS_EXCEPTIONS                                           = true;
    public const IS_HTML                                                  = true;
    public const PHPMAILER_SMTP_DEBUG_NO_OUTPUT                           = 0;
    public const PHPMAILER_SMTP_DEBUG_COMMANDS                            = 1;
    public const PHPMAILER_SMTP_DEBUG_DATA_AND_COMMANDS                   = 2;
    public const PHPMAILER_SMTP_DEBUG_DATA_COMMANDS_AND_CONNECTION_STATUS = 3;
    public const PHPMAILER_SMTP_DEBUG_LOW_LEVEL_DATA_OUTPUT               = 4;

    private PHPMailer     $mailer;
    private ConfigService $configService;
    private ILogger       $logger;

    public function __construct(
        Legacy $legacy
        , ConfigService $configService
        , ILogger $logger
    ) {
        $this->logger = $logger;
        // TODO put the config in a config file
        $this->mailer = new PHPMailer(EmailService::HAS_EXCEPTIONS);

        //Server settings
        $this->mailer->SMTPDebug = EmailService::PHPMAILER_SMTP_DEBUG_DATA_COMMANDS_AND_CONNECTION_STATUS;
        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $configService->getValue("email_user");
        $this->mailer->Password   = $configService->getValue("email_password");
        $this->mailer->Host       = $configService->getValue("email_smtp_host");
        $this->mailer->SMTPSecure = 'ssl';
        $this->mailer->Port       = 465;

        $this->mailer->setFrom(
            $legacy->getApplication()->get("email")
            , $legacy->getApplication()->get("name")
        );
        $this->mailer->addReplyTo(
            $legacy->getApplication()->get("email")
            , $legacy->getApplication()->get("name")
        );
        $this->mailer->Debugoutput = function ($message) {
            $this->logger->debug($message);
        };

        // Global Config
        $this->mailer->isHTML(EmailService::IS_HTML);   // Set email format to HTML
        $this->configService = $configService;

    }

    public function addRecipent(string $name, string $email): void {
        $this->mailer->addAddress($email, $name);     // Add a recipient
    }

    public function addAttachment(string $path, string $name = ""): void {
        // Attachments
        $this->mailer->addAttachment($path, $name);    // Optional name
    }

    public function setSubject(string $subject): void {
        $this->mailer->Subject = $subject;
    }

    public function send(int $delay = 0): bool {
        $recipients = $this->mailer->getAllRecipientAddresses();

        if (0 === count($recipients)) return false;
        if (false === $this->hasSubject()) return false;
        if (false === $this->hasBody()) return false;

        $this->replaceDebugMails();

        try {
            sleep($delay);
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            $this->logger->debug($e->getTraceAsString());
            return false;
        } finally {
            $this->clearAll();
        }

    }

    private function hasSubject(): bool {
        return "" !== trim($this->mailer->Subject);
    }

    private function hasBody(): bool {
        return "" !== trim($this->mailer->Body);
    }

    private function replaceDebugMails(): void {
        if (true === $this->configService->getValue("debug", true)) {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->addAddress(
                $this->configService->getValue("email_user")
                , $this->configService->getValue("email_user")
            );
        }

    }

    private function clearAll(): void {
        $this->mailer->clearAddresses();
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->setBody("");
        $this->setAlternativeBody("");
    }

    public function setBody(string $body): void {
        $this->mailer->Body = $body;
    }

    public function setAlternativeBody(string $body): void {
        $this->mailer->AltBody = $body;
    }

}
