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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash;
use Keestash\Api\AbstractApi;

use Keestash\Core\Service\File\Icon\IconService;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\File\IFileRepository;
use KSP\L10N\IL10N;

class Get extends AbstractApi {

    private IFileRepository $fileRepository;
    private NodeRepository  $nodeRepository;
    private FileRepository  $nodeFileRepository;
    private IconService     $iconService;

    public function __construct(
        IL10N $l10n
        , IFileRepository $uploadFileRepository
        , NodeRepository $nodeRepository
        , FileRepository $nodeFileRepository
        , IconService $iconService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->fileRepository     = $uploadFileRepository;
        $this->nodeRepository     = $nodeRepository;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->iconService        = $iconService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $nodeId = $this->getParameters()["nodeId"] ?? null;

        if (null === $nodeId) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no node id given")
                ]
            );
            return;
        }

        $nodeExists = $this->nodeRepository->exists((int) $nodeId);
        if (false === $nodeExists) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no node found")
                ]
            );
            return;
        }

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if ($node->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
            throw new PasswordManagerException();
        }

        $list = $this->nodeFileRepository->getFilesPerNode(
            $node
            , NodeFile::FILE_TYPE_ATTACHMENT
        );

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "fileList" => $list
                , "icons"  => $this->addIcons($list)
            ]
        );

    }

    private function addIcons(ArrayList $fileList): array {
        $icons    = [];
        $assetDir = Keestash::getServer()->getAssetRoot();
        $svgDir   = str_replace("//", "/", "$assetDir/svg/");

        /** @var NodeFile $nodeFile */
        foreach ($fileList as $nodeFile) {
            $icons[$nodeFile->getFile()->getId()] = file_get_contents(
                $svgDir . $this->iconService->getIconForExtension($nodeFile->getFile()->getExtension())
            );
        }

        return $icons;
    }

    public function afterCreate(): void {

    }

}
