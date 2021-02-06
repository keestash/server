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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Exception;
use Keestash\Core\Repository\Instance\InstanceDB;
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
    private InstanceDB    $instanceDb;
    private HashTable     $recipients;
    private HashTable     $carbonCopy;
    private HashTable     $blindCarbonCopy;

    public function __construct(
        Legacy $legacy
        , ConfigService $configService
        , ILogger $logger
        , InstanceDB $instanceDB
    ) {
        $this->logger          = $logger;
        $this->instanceDb      = $instanceDB;
        $this->mailer          = new PHPMailer(EmailService::HAS_EXCEPTIONS);
        $this->recipients      = new HashTable();
        $this->carbonCopy      = new HashTable();
        $this->blindCarbonCopy = new HashTable();

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

    public function addRecipient(string $name, string $email): void {
        $this->recipients->put($name, $email);
    }

    public function addCarbonCopy(string $name, string $email): void {
        $this->carbonCopy->put($name, $email);
    }

    public function addBlindCarbonCopy(string $name, string $email): void {
        $this->blindCarbonCopy->put($name, $email);
    }

    public function addAttachment(string $path, string $name = ""): void {
        // Attachments
        $this->mailer->addAttachment($path, $name);    // Optional name
    }

    public function setSubject(string $subject): void {
        $this->mailer->Subject = $subject;
    }

    public function send(int $delay = 0): bool {
        $this->putAllReceivers();
        $recipients = $this->mailer->getAllRecipientAddresses();

        if (0 === count($recipients)) return false;
        if (false === $this->hasSubject()) return false;
        if (false === $this->hasBody()) return false;

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

    private function putAllReceivers(): void {
        $productionModeDate = $this->instanceDb->getOption(InstanceDB::OPTION_NAME_PRODUCTION_MODE);
        if (null === $productionModeDate || "" === $productionModeDate) {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress(
                $this->configService->getValue("email_user")
                , $this->configService->getValue("email_user")
            );
            return;
        }

        foreach ($this->recipients->toArray() as $name => $mail) {
            $this->mailer->addAddress($mail, $name);
        }

        foreach ($this->carbonCopy->toArray() as $name => $mail) {
            $this->mailer->addCC($mail, $name);
        }

        foreach ($this->blindCarbonCopy->toArray() as $name => $mail) {
            $this->mailer->addBCC($mail, $name);
        }

    }

    private function hasSubject(): bool {
        return "" !== trim($this->mailer->Subject);
    }

    private function hasBody(): bool {
        return "" !== trim($this->mailer->Body);
    }

    private function clearAll(): void {
        $this->mailer->clearAddresses();
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->recipients      = new HashTable();
        $this->carbonCopy      = new HashTable();
        $this->blindCarbonCopy = new HashTable();
        $this->setBody("");
        $this->setAlternativeBody("");
        $this->setSubject('');
    }

    public function setBody(string $body): void {
        $this->mailer->Body = $body;
    }

    public function setAlternativeBody(string $body): void {
        $this->mailer->AltBody = $body;
    }

}
