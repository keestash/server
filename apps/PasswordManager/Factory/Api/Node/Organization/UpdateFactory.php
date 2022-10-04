<?php
declare(strict_types=1);

namespace KSA\PasswordManager\Factory\Api\Node\Organization;

use Interop\Container\ContainerInterface;
use KSA\PasswordManager\Api\Node\Organization\Update;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Logger\ILogger;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UpdateFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): Update {
        return new Update(
            $container->get(NodeRepository::class)
            , $container->get(IOrganizationRepository::class)
            , $container->get(ILogger::class)
            , $container->get(OrganizationRepository::class)
            , $container->get(IEventService::class)
        );
    }

}