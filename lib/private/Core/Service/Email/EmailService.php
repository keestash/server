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
use KSP\Core\Service\Email\IEmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Psr\Log\LoggerInterface;

class EmailService implements IEmailService {

    public const HAS_EXCEPTIONS = true;
    public const IS_HTML        = true;

    private PHPMailer $mailer;
    private HashTable $recipients;
    private HashTable $carbonCopy;
    private HashTable $blindCarbonCopy;

    public function __construct(
        private readonly ConfigService     $configService
        , private readonly LoggerInterface $logger
        , private readonly InstanceDB      $instanceDB
    ) {
        $this->mailer          = new PHPMailer(EmailService::HAS_EXCEPTIONS);
        $this->recipients      = new HashTable();
        $this->carbonCopy      = new HashTable();
        $this->blindCarbonCopy = new HashTable();
    }

    private function putDefaults(): void {
        //Server settings
        $this->mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = (string) $this->configService->getValue("email_user");
        $this->mailer->Password   = (string) $this->configService->getValue("email_password");
        $this->mailer->Host       = (string) $this->configService->getValue("email_smtp_host");
        $this->mailer->SMTPSecure = strtolower(
            (string) $this->configService->getValue("email_protocol")
        );
        $this->mailer->Port       = (int) $this->configService->getValue("email_port");

        $this->mailer->setFrom(
            (string) $this->configService->getValue("email_user")
            , (string) $this->configService->getValue("email_user_name")
        );
        $this->mailer->addReplyTo(
            (string) $this->configService->getValue("email_user")
            , (string) $this->configService->getValue("email_user_name")
        );
        $this->mailer->Debugoutput = function ($message) {
            $this->logger->debug($message);
        };

        // Global Config
        $this->mailer->isHTML(EmailService::IS_HTML);   // Set email format to HTML
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

        try {
            sleep($delay);

            $this->putDefaults();
            $this->putAllReceivers();
            $recipients = $this->mailer->getAllRecipientAddresses();

            if (0 === count($recipients)) {
                $this->logger->info('no recipients given. Skipping mail send');
                return false;
            }
            if (false === $this->hasSubject()) {
                $this->logger->info('no subject given. Skipping mail send');
                return false;
            }
            if (false === $this->hasBody()) {
                $this->logger->info('no body given. Skipping mail send');
                return false;
            }

            $sendAllowed = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_NOTIFICATIONS_SEND_ALLOWED);

            $this->logger->debug('notifications allowed', ['allowed' => $sendAllowed, 'allowedBoolean' => $sendAllowed === 'true']);
            if ($sendAllowed === 'true') {
                $this->mailer->send();
            }
            return true;
        } catch (Exception $e) {
            $this->logger->error('error with mail sending', ['e' => $e]);
            return false;
        } finally {
            $this->clearAll();
        }

    }

    private function putAllReceivers(): void {
        $environment = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_ENVIRONMENT);
        if ('production' !== $environment) {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress(
                (string) $this->configService->getValue("email_user")
                , (string) $this->configService->getValue("email_user")
            );

            $this->mailer->Body = "######## DEV ########<br><br>" . $this->mailer->Body;
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
