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

namespace KSA\PasswordManager\Controller\PublicShare;


use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PublicShareController extends FullscreenAppController {

    public const TEMPLATE_NAME_PUBLIC_SHARE_NO_PASSWORD = "public_share_no_password.twig";

    private NodeRepository            $nodeRepository;
    private PublicShareRepository     $shareRepository;
    private IUserRepository           $userRepository;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , IAppRenderer $appRenderer
        , IL10N $l10n
        , NodeRepository $nodeRepository
        , PublicShareRepository $shareRepository
        , IUserRepository $userRepository
    ) {
        parent::__construct($appRenderer);

        $this->nodeRepository   = $nodeRepository;
        $this->shareRepository  = $shareRepository;
        $this->userRepository   = $userRepository;
        $this->templateRenderer = $templateRenderer;
        $this->translator       = $l10n;
    }

    public function run(ServerRequestInterface $request): string {
        $hash = $request->getAttribute("hash");

        $content = null;
        if (null === $hash) {
            return $this->renderNoPassword();
        }

        $publicShare = $this->shareRepository->getShare($hash);

        if (
            null === $publicShare
            || true === $publicShare->isExpired()
        ) {
            return $this->renderNoPassword();
        }

        $node = $this->nodeRepository->getNode($publicShare->getNodeId());

        // TODO does the user has access to the node?
        //   aka do he own/is shared to him/her?

        $content = $this->renderPassword($publicShare, $node);
        return $content;
    }

    private function renderNoPassword(): string {
        return $this->templateRenderer->render(
            'passwordManager::public_share_no_password'
            , []
        );
    }

    private function renderPassword(PublicShare $publicShare, Credential $node): string {

        return $this->templateRenderer->render(
            'passwordManager::public_share_no_password'
            , [
                "password"              => $this->translator->translate("Password")
                , "passwordPlaceholder" => $node->getPassword()->getPlaceholder()
                , "description"         => $this->translator->translate("This password is shared with you by {$node->getUser()->getName()}.")
                , "hash"                => $publicShare->getHash()
                , "userNameLabel"       => $this->translator->translate("Username")
                , "userNamePlaceholder" => $this->translator->translate("Username")
                , "userNameContent"     => $node->getUsername()
                , "passwordSmall"       => $this->translator->translate("Please handle the password shared with you sensitively.")
            ]
        );

    }

}
