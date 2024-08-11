<?php

namespace KSA\Login\Factory\Event;

use KSA\Login\Event\ApplicationEndedEventListener;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\Router\ApiLogServiceInterface;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ApplicationEndedEventListenerFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): ApplicationEndedEventListener {
        return new ApplicationEndedEventListener(
            $container->get(LoggerInterface::class),
            $container->get(ApiLogServiceInterface::class),
            $container->get(Config::class),
            $container->get(ICollectorService::class)
        );
    }

}