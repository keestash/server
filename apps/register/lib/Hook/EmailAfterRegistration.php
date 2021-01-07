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

namespace KSA\Register\Hook;

use Keestash;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Legacy\Legacy;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Hook\IHook;
use KSP\Hook\IHookCache;
use KSP\L10N\IL10N;

class EmailAfterRegistration implements IHook {

    private ITemplateManager $templateManager;
    private EmailService     $emailService;
    private Legacy           $legacy;
    private IL10N            $translator;
    private ILogger          $logger;

    public function __construct(
        ITemplateManager $templateManager
        , EmailService $emailService
        , Legacy $legacy
        , IL10N $l10n
        , ILogger $logger
    ) {
        $this->templateManager = $templateManager;
        $this->emailService    = $emailService;
        $this->legacy          = $legacy;
        $this->translator      = $l10n;
        $this->logger          = $logger;
    }

    public function performAction(...$parameters): bool {
        $this->logger->debug("please implement me :( " . EmailAfterRegistration::class);
        return true;
        $user = $parameters[0][0] ?? null;

        if (null === $user) {
            $this->logger->error("There is no user, can not send mail. Parameters are: " . (json_encode($parameters)));
            return false;
        }

        if (!$user instanceof IUser) {
            $this->logger->error("passed argument is not an user, can not send mail. Parameters are: " . (json_encode($parameters)));
            return false;
        }

        $appName = $this->legacy->getApplication()->get("name");
        $this->templateManager->replace(
            "welcome_email.twig",
            [
                "logoPath"               => Keestash::getBaseURL(false) . "/asset/img/logo.png"
                , "appName"              => $appName
                , "appSlogan"            => $this->legacy->getApplication()->get("slogan")
                , "welcomeToApp"         => $this->translator->translate("Welcome To $appName")
                , "callToAction"         => $this->translator->translate("You are enabled now for $appName. Log In and start using.")
                , "ctaButtonText"        => $this->translator->translate("Log In To $appName")
                , "thisEmailIsSentToYou" => $this->translator->translate("This email was sent to {$user->getEmail()} to reset your password. If you did not request a reset, please ignore this mail or let us know.")
            ]
        );
        $rendered = $this->templateManager->render("welcome_email.twig");
        $this->emailService->addRecipent($user->getName(), $user->getEmail());
        $this->emailService->setSubject($this->translator->translate("You are registered for $appName"));
        $this->emailService->setBody($rendered);
        return $this->emailService->send();
    }

    public function getHookCache(): ?IHookCache {
        return null;
    }

}
