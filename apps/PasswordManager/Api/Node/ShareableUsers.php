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

namespace KSA\PasswordManager\Api\Node;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Exception\InvalidParameterException;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Share\Share;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ShareableUsers
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class ShareableUsers implements RequestHandlerInterface {

    private IL10N           $translator;
    private IUserRepository $userRepository;
    private NodeRepository  $nodeRepository;
    private FileManager     $fileManager;
    private RawFileService  $rawFileService;
    private FileService     $fileService;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , NodeRepository $nodeRepository
        , FileManager $fileManager
        , RawFileService $rawFileService
        , FileService $fileService
    ) {
        $this->translator     = $l10n;
        $this->userRepository = $userRepository;
        $this->nodeRepository = $nodeRepository;
        $this->fileManager    = $fileManager;
        $this->rawFileService = $rawFileService;
        $this->fileService    = $fileService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $nodeId = $request->getAttribute("nodeId");
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $nodeId) {
            throw new InvalidParameterException();
        }

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (null === $node || $node->getUser()->getId() !== $token->getUser()->getId()) {
            throw new InvalidParameterException();
        }

        $all          = $this->userRepository->getAll();
        $all          = $this->excludeInvalidUsers($node, $all);
        $pictureTable = $this->createPictureTable($all);

        if (null === $all) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no users found")
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "user_list"  => $all
                , "pictures" => $pictureTable
            ]
        );
    }

    private function excludeInvalidUsers(?Node $node, ?ArrayList $users): ?ArrayList {
        if (null === $node) return null;
        if (null === $users) return null;

        /** @var Share $share */
        foreach ($node->getSharedTo() as $share) {
            /** @var IUser $user */
            foreach ($users as $key => $user) {
                if ($share->getUser()->getId() === $user->getId()) {
                    $users->remove($key);
                }

                if ($user->getId() === IUser::SYSTEM_USER_ID) {
                    $users->remove($key);
                }
            }
        }

        /** @var IUser $user */
        foreach ($users as $key => $user) {

            if ($user->getId() === $node->getUser()->getId()) {
                $users->remove($key);
            }

            if (true === $user->isLocked()) {
                $users->remove($key);
            }

        }

        return $users;

    }

    private function createPictureTable(ArrayList $userList): array {
        $pictureTable = [];

        /** @var IUser $user */
        foreach ($userList as $key => $user) {

            $file = $this->fileManager->read(
                $this->rawFileService->stringToUri(
                    $this->fileService->getProfileImagePath($user)
                )
            );

            // TODO make better ?!
            if (null === $file) {
                $file = $this->fileService->getDefaultImage();
            }

//            $userImage                    = $this->rawFileService->stringToBase64($path);
            $userImage                    = $this->rawFileService->stringToBase64("{$file->getDirectory()}/{$file->getName()}");
            $pictureTable[$user->getId()] = $userImage;
        }
        return $pictureTable;
    }

}
