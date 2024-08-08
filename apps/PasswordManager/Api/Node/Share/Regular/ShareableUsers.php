<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or
 * indirectly through a Keestash authorized reseller or distributor (a "Reseller"). Please read this EULA agreement
 * carefully before completing the installation process and using the Keestash software. It provides a license to use
 * the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Api\Node\Share\Regular;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Exception\InvalidParameterException;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IJWTService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ShareableUsers
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
final readonly class ShareableUsers implements RequestHandlerInterface {

    public function __construct(
        private IUserRepository   $userRepository
        , private NodeRepository  $nodeRepository
        , private LoggerInterface $logger
        , private IJWTService     $jwtService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $start  = microtime(true);
        $nodeId = $request->getAttribute("nodeId");
        $query  = $request->getAttribute("query");

        if (null === $nodeId) {
            throw new InvalidParameterException();
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $all = $this->userRepository->searchUsers((string) $query);
        $all = $this->excludeInvalidUsers($node, $all);

        $duration = microtime(true) - $start;
        $this->logger->debug('all users duration: ' . $duration);
        return new JsonResponse(
            [
                "user_list"  => $all
                , "duration" => $duration
            ]
            , IResponse::OK
        );
    }

    private function excludeInvalidUsers(?Node $node, ?ArrayList $users): ArrayList {
        $usersFormatted = new ArrayList();
        if (null === $node) return $usersFormatted;
        if (null === $users) return $usersFormatted;

        /** @var IUser $user */
        foreach ($users as $user) {

            if ($node->isSharedTo($user)) {
                continue;
            }

            if ($user->getId() === IUser::SYSTEM_USER_ID) {
                continue;
            }

            if ($user->getId() === $node->getUser()->getId()) {
                continue;
            }

            if (true === $user->isLocked()) {
                continue;
            }

            $user->setJWT(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_USER
                        , (string) $user->getId()
                    )
                )
            );

            $usersFormatted->add($user);
        }

        return $usersFormatted;

    }

}
