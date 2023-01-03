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

namespace Keestash\Factory\Core\Event\Listener;

use Keestash\Core\DTO\Event\Listener\SendSummaryMail;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\L10N\IL10N;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class SendSummaryMailListenerFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): SendSummaryMail {
        return new SendSummaryMail(
            $container->get(IMailLogRepository::class)
            , $container->get(IEmailService::class)
            , $container->get(IUserRepository::class)
            , $container->get(LoggerInterface::class)
            , $container->get(IConfigService::class)
            , $container->get(TemplateRendererInterface::class)
            , $container->get(IL10N::class)
        );
    }

}