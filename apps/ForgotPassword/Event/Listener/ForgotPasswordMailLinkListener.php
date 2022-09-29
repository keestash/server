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

namespace KSA\ForgotPassword\Event\Listener;

use Keestash\Legacy\Legacy;
use KSA\ForgotPassword\Event\ForgotPasswordEvent;
use KSA\ForgotPassword\Exception\ForgotPasswordException;
use KSP\Core\Manager\EventManager\IEvent;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Ramsey\Uuid\Uuid;

class ForgotPasswordMailLinkListener implements IListener {

    private Legacy                    $legacy;
    private IHTTPService              $httpService;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;
    private IEmailService             $emailService;

    public function __construct(
        Legacy                      $legacy
        , IHTTPService              $httpService
        , TemplateRendererInterface $templateRenderer
        , IL10N                     $translator
        , IEmailService             $emailService
    ) {
        $this->legacy           = $legacy;
        $this->httpService      = $httpService;
        $this->templateRenderer = $templateRenderer;
        $this->translator       = $translator;
        $this->emailService     = $emailService;
    }

    public function execute(IEvent $event): void {

        if (false === ($event instanceof ForgotPasswordEvent)) {
            throw new ForgotPasswordException();
        }

        $baseUrl   = $this->httpService->getBaseURL(true, true);
        $uuid      = Uuid::uuid4();
        $appName   = $this->legacy->getApplication()->get("name");
        $appSlogan = $this->legacy->getApplication()->get("slogan");
        $ctaLink   = $baseUrl . "/reset_password/" . $uuid;

        $rendered = $this->templateRenderer->render(
            'forgotPassword::forgot_email'
            , [
                // changeable
                "appName"          => $appName
                , "logoAlt"        => $appName
                , "appSlogan"      => $appSlogan

                // TODO load this from theming
                , "bodyBackground" => "#f8f8f8"
                , "themeColor"     => "#269dff"

                // strings
                , "mailTitle"      => $this->translator->translate("Reset Password")
                , "salutation"     => $this->translator->translate("Dear {$event->getUser()->getName()},")
                , "text"           => $this->translator->translate("This email was sent to {$event->getUser()->getEmail()} to reset your password. If you did not request a reset, please ignore this mail or let us know.")
                , "ctaButtonText"  => $this->translator->translate("Reset Password")
                , "thanksText"     => $this->translator->translate("-Thanks $appName")
                , "poweredByText"  => $this->translator->translate("Powered By $appName")

                // values
                , "logoPath"       => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "ctaLink"        => $ctaLink
                , "baseUrl"        => $baseUrl
                , "hasUnsubscribe" => false
            ]
        );

        $this->emailService->addRecipient(
            $event->getUser()->getName()
            , $event->getUser()->getEmail()
        );

        $this->emailService->setSubject(
            $this->translator->translate("Resetting Password")
        );
        $this->emailService->setBody($rendered);
        $this->emailService->send();

    }

}