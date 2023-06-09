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

use Keestash\Core\System\Application;
use KSA\ForgotPassword\Event\ForgotPasswordEvent;
use KSA\ForgotPassword\Exception\ForgotPasswordException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Ramsey\Uuid\Uuid;

class ForgotPasswordMailLinkListener implements IListener {

    private Application               $legacy;
    private IHTTPService              $httpService;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;
    private IEmailService             $emailService;

    public function __construct(
        Application                 $legacy
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

        $this->emailService->addRecipient(
            $event->getUser()->getName()
            , $event->getUser()->getEmail()
        );

        $this->emailService->setSubject(
            $this->translator->translate("Resetting Password")
        );
        $this->emailService->setBody('');
        $this->emailService->send();

    }

}