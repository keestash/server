<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\Register\Factory\Event\Listener;

use Keestash\Core\System\Application;
use KSA\Register\Event\EmailAfterRegistration;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\L10N\IL10N;
use Psr\Log\LoggerInterface as ILogger;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class EmailAfterRegistrationListenerFactory {

    public function __invoke(ContainerInterface $container): EmailAfterRegistration {
        return new EmailAfterRegistration(
            $container->get(TemplateRendererInterface::class)
            , $container->get(Application::class)
            , $container->get(IL10N::class)
            , $container->get(ILogger::class)
            , $container->get(IHTTPService::class)
            , $container->get(IEmailService::class)
        );
    }

}