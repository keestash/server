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
use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\File\Icon\IIconService;
use KSP\L10N\IL10N;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    private NodeRepository $nodeRepository;
    private FileRepository $nodeFileRepository;
    private IIconService   $iconService;
    private IL10N          $translator;
    private Config         $config;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , FileRepository $nodeFileRepository
        , IIconService $iconService
        , Config $config
    ) {
        $this->translator         = $l10n;
        $this->nodeRepository     = $nodeRepository;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->iconService        = $iconService;
        $this->config             = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $nodeId = $request->getAttribute("nodeId");
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $nodeId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no node id given")
                ]
            );
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no node found")
                ]
            );
        }

        if (false === $this->hasAccess($token->getUser(), $node)) {
            throw new PasswordManagerException();
        }

        $list = $this->nodeFileRepository->getFilesPerNode(
            $node
            , NodeFile::FILE_TYPE_ATTACHMENT
        );

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "fileList" => $list
                , "icons"  => $this->addIcons($list)
            ]
        );

    }

    private function addIcons(ArrayList $fileList): array {
        $icons    = [];
        $assetDir = (string) $this->config->get(Keestash\ConfigProvider::ASSET_PATH);
        $svgDir   = str_replace("//", "/", "$assetDir/svg/");

        /** @var NodeFile $nodeFile */
        foreach ($fileList as $nodeFile) {
            $icons[$nodeFile->getFile()->getId()] = file_get_contents(
                $svgDir . $this->iconService->getIconForExtension($nodeFile->getFile()->getExtension())
            );
        }

        return $icons;
    }

    private function hasAccess(IUser $user, Node $node): bool {
        if ($node->getUser()->getId() === $user->getId()) return true;
        if (true === $node->isSharedTo($user)) return true;
        return false;
    }

}
