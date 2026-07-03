<?php
declare(strict_types=1);

namespace Keestash\Factory\Command;

use Psr\Container\ContainerInterface;
use Keestash\Command\GenerateOpenApi;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GenerateOpenApiFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container,
                           $requestedName,
        ?array             $options = null
    ): GenerateOpenApi {
        return new GenerateOpenApi();
    }

}
