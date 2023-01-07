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

namespace KSA\PasswordManager\Factory\Event\Listener;

use Keestash\Core\System\Application;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class AfterRegistrationFactory {

    public function __invoke(ContainerInterface $container): AfterRegistration {
        return new AfterRegistration(
            $container->get(IKeyService::class)
            , $container->get(LoggerInterface::class)
            , $container->get(NodeService::class)
            , $container->get(NodeRepository::class)
            , $container->get(CredentialService::class)
            , $container->get(Application::class)
            , $container->get(IEmailService::class)
            , $container->get(TemplateRendererInterface::class)
            , $container->get(IL10N::class)
            , $container->get(IMailLogRepository::class)
        );
    }

}