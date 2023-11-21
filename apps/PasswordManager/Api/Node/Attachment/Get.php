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

namespace KSA\PasswordManager\Api\Node\Attachment;

use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Get implements RequestHandlerInterface {

    public function __construct(
        private readonly NodeRepository     $nodeRepository
        , private readonly FileRepository   $nodeFileRepository
        , private readonly LoggerInterface  $logger
        , private readonly IActivityService $activityService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $nodeId = $request->getAttribute("nodeId");

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            $this->logger->info('no node found', ['exception' => $exception, 'nodeId' => $nodeId]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $list = $this->nodeFileRepository->getFilesPerNode(
            $node
            , NodeFile::FILE_TYPE_ATTACHMENT
        );

        $this->activityService->insertActivityWithSingleMessage(
            ConfigProvider::APP_ID
            , (string) $node->getId()
            , sprintf(
                'files listed by %s'
                , $token->getUser()->getName()
            )
        );

        return new JsonResponse(
            [
                "fileList" => $list
            ]
            , IResponse::OK
        );
    }

}
