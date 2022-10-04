<?php
declare(strict_types=1);

namespace KSA\PasswordManager\Factory\Service;

use Interop\Container\ContainerInterface;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Logger\ILogger;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NodeEncryptionServiceFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): NodeEncryptionService {
        return new NodeEncryptionService(
            $container->get(EncryptionService::class)
            , $container->get(IKeyService::class)
            , $container->get(ILogger::class)
        );
    }

}