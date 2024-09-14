<?php
declare(strict_types=1);

namespace KSA\Register\Factory\Command;

use KSA\Register\Command\DeleteUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteUserFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): DeleteUser {
        return new DeleteUser(
            $container->get(IUserRepositoryService::class)
            , $container->get(IUserRepository::class)
        );
    }

}