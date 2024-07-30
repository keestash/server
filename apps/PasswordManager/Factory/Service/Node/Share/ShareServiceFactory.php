<?php
declare(strict_types=1);

namespace KSA\PasswordManager\Factory\Service\Node\Share;

use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ShareServiceFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container,
                           $requestedName,
        ?array             $options = null
    ): ShareService {
        return new ShareService(
            $container->get(NodeRepository::class),
            $container->get(IUserRepository::class),
            $container->get(IUserService::class)
        );
    }

}
