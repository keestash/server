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

namespace Keestash\Core\Service;

use doganoo\PHPUtil\Log\FileLogger;
use Exception;
use Keestash\Legacy\Legacy;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService {

    public const HAS_EXCEPTIONS                                           = true;
    public const IS_HTML                                                  = true;
    public const PHPMAILER_SMTP_DEBUG_NO_OUTPUT                           = 0;
    public const PHPMAILER_SMTP_DEBUG_COMMANDS                            = 1;
    public const PHPMAILER_SMTP_DEBUG_DATA_AND_COMMANDS                   = 2;
    public const PHPMAILER_SMTP_DEBUG_DATA_COMMANDS_AND_CONNECTION_STATUS = 3;
    public const PHPMAILER_SMTP_DEBUG_LOW_LEVEL_DATA_OUTPUT               = 4;

    private $mailer = null;

    public function __construct(Legacy $legacy) {
        // TODO put the config in a config file
        $this->mailer = new PHPMailer(EmailService::HAS_EXCEPTIONS);

        //Server settings
        $this->mailer->SMTPDebug = EmailService::PHPMAILER_SMTP_DEBUG_DATA_COMMANDS_AND_CONNECTION_STATUS;
        $this->mailer->isSMTP();                                                                                                                                                                                                                                                                                                                                                                                                                                    // Set mailer to use SMTP
        $this->mailer->Host       = 'smtp.mail.eu-west-1.awsapps.com';                                                                                                                                                                                                                                                                                                                                                                                                    // Specify main and backup SMTP servers
        $this->mailer->SMTPAuth = true;                                                                                                                                                                                                                                                                                                                                                                                                                             // Enable SMTP authentication
        $this->mailer->Username = 'info@ucar-solutions.de';                                                                                                                                                                                                                                                                                                                                                                                                         // SMTP username
        $this->mailer->Password = 'buLfXZLQvZ3S';                                                                                                                                                                                                                                                                                                                                                                                                                   // SMTP password
        $this->mailer->SMTPSecure = 'ssl';                                                                                                                                                                                                                                                                                                                                                                                                                          // Enable TLS encryption, `ssl` also accepted
        $this->mailer->Port       = 465;                                                                                                                                                                                                                                                                                                                                                                                                                            // TCP port to connect to

        //Recipients
        $this->mailer->setFrom('info@ucar-solutions.de', $legacy->getApplication()->get("name"));
        $this->mailer->addReplyTo('info@ucar-solutions.de', $legacy->getApplication()->get("name"));
        $this->mailer->addBCC('info@ucar-solutions.de');
        $this->mailer->Debugoutput = function ($message) {
            FileLogger::debug($message);
        };

        // Global Config
        $this->mailer->isHTML(EmailService::IS_HTML);                                                                                                                                                                                                                                                                                                                                                                                                               // Set email format to HTML

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
//            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            FileLogger::debug($e->getTraceAsString());
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

    private function replaceDebugMails() {
        // TODO check if we are on debug
        if (1 === 1) {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->addAddress("dogan@dogan-ucar.de", "Dogan Ucar");
        }

    }

    private function clearAll() {
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