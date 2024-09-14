<?php
declare(strict_types=1);

namespace Keestash\Factory\Api;

use Keestash\Api\PingHandler;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class PingHandlerFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): PingHandler {
        return new PingHandler(
            $container->get(LoggerInterface::class)
        );
    }

}
