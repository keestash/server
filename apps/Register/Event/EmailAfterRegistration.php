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

namespace KSA\Register\Event;

use DateTime;
use doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException;
use doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Legacy\Legacy;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IListener;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Symfony\Contracts\EventDispatcher\Event;

class EmailAfterRegistration implements IListener {

    public const TEMPLATE_NAME = "mail.twig";

    private TemplateRendererInterface $templateManager;
    private EmailService              $emailService;
    private Legacy                    $legacy;
    private IL10N                     $translator;
    private ILogger                   $logger;
    private HTTPService               $httpService;

    public function __construct(
        TemplateRendererInterface $templateManager
        , EmailService $emailService
        , Legacy $legacy
        , IL10N $l10n
        , ILogger $logger
        , HTTPService $httpService
    ) {
        $this->templateManager = $templateManager;
        $this->emailService    = $emailService;
        $this->legacy          = $legacy;
        $this->translator      = $l10n;
        $this->logger          = $logger;
        $this->httpService     = $httpService;
    }

    /**
     * @param Event|UserCreatedEvent $event
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     */
    public function execute(Event $event): void {
        $this->logger->debug('implement me :(');
        return; // TODO implement
        if (
            $event->getUser()->getId() === IUser::SYSTEM_USER_ID
            || $event->getUser()->getName() === IUser::DEMO_USER_NAME
        ) {
            return;
        }
        $appName = $this->legacy->getApplication()->get("name");
        $this->templateManager->replace(
            EmailAfterRegistration::TEMPLATE_NAME,
            [
                "title"              => $this->translator->translate("Welcome To $appName")
                , "keestashLogoHref" => $this->httpService->getBaseURL(false) . "/asset/img/logo.png"
                , "keestashLogoAlt"  => $appName
                , "salutation"       => $this->translator->translate("Dear {$event->getUser()->getFirstName()},")
                , "mainInfo"         => $this->translator->translate("You are enabled now for $appName. Log In and start using.")
                , "detailText"       => $this->translator->translate("You are successfully registered for $appName.")
                , "ctaHref"          => $this->httpService->getBaseURL(false) . "/index.php/login/"
                , "ctaText"          => $this->translator->translate("Login")
                , "hasCta"           => true
                , "postCtaFirst"     => $this->translator->translate("")
                , "postCtaLink"      => $this->httpService->getBaseURL(false)
                , "postCtaSecond"    => $this->translator->translate("Follow this link if you have any questions.")
                , "thankYou"         => $this->translator->translate("Regards,")
                , "thankYouName"     => $appName
                , "hasSocialMedia"   => false
                , "copyRightText"    => (new DateTime())->format("Y") . " " . $appName
                , "copyRightHref"    => $this->httpService->getBaseURL(false)
            ]
        );
        $rendered = $this->templateManager->render(EmailAfterRegistration::TEMPLATE_NAME);
        $this->emailService->addRecipient($event->getUser()->getName(), $event->getUser()->getEmail());
        $this->emailService->setSubject($this->translator->translate("You are registered for $appName"));
        $this->emailService->setBody($rendered);
        $this->emailService->send();

    }

}
