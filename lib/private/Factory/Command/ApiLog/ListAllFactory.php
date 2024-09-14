<?php

namespace Keestash\Factory\Command\ApiLog;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\ApiLog\ListAll;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ListAllFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): ListAll {
        return new ListAll(
            $container->get(IApiLogRepository::class),
            $container->get(IDateTimeService::class)
        );
    }

}