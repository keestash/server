<?php
declare(strict_types=1);

namespace Keestash\ThirdParty\SimpleRbac\Factory\Service;

use Keestash\ThirdParty\SimpleRbac\Repository\RBACRepositoryInterface;
use Keestash\ThirdParty\SimpleRbac\Service\RBACService;
use Keestash\ThirdParty\SimpleRbac\Service\RBACServiceInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RBACServiceFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): RBACServiceInterface {
        return new RBACService(
            $container->get(RBACRepositoryInterface::class)
        );
    }

}