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
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class PublicShareController extends FullscreenAppController {

    public const TEMPLATE_NAME_PUBLIC_SHARE_NO_PASSWORD     = "public_share_no_password.twig";
    public const TEMPLATE_NAME_PUBLIC_SHARE_SINGLE_PASSWORD = "public_share_single.twig";

    private NodeRepository        $nodeRepository;
    private PublicShareRepository $shareRepository;
    private IUserRepository       $userRepository;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , NodeRepository $nodeRepository
        , PublicShareRepository $shareRepository
        , IUserRepository $userRepository
    ) {
        parent::__construct($templateManager, $l10n);

        $this->nodeRepository  = $nodeRepository;
        $this->shareRepository = $shareRepository;
        $this->userRepository  = $userRepository;
    }

    public function onCreate(): void {

    }

    public function create(): void {
        $hash = $this->getParameter("hash");

        $content = null;
        if (null === $hash) {
            $this->setAppContent(
                $this->renderNoPassword()
            );
            return;
        }

        $publicShare = $this->shareRepository->getShare($hash);

        if (
            null === $publicShare
            || true === $publicShare->isExpired()
        ) {
            $this->setAppContent(
                $this->renderNoPassword()
            );
            return;
        }

        $node = $this->nodeRepository->getNode($publicShare->getNodeId());

        // TODO does the user has access to the node?
        //   aka do he own/is shared to him/her?

        $content = $this->renderPassword($publicShare, $node);

        parent::setAppContent(
            $content

        );
    }

    private function renderNoPassword(): string {
        $this->getTemplateManager()->replace(
            PublicShareController::TEMPLATE_NAME_PUBLIC_SHARE_NO_PASSWORD
            , []
        );
        return $this->getTemplateManager()->render(PublicShareController::TEMPLATE_NAME_PUBLIC_SHARE_NO_PASSWORD);
    }

    private function renderPassword(PublicShare $publicShare, Credential $node): string {

        $owner = $node->getUser();

        $this->getTemplateManager()->replace(
            PublicShareController::TEMPLATE_NAME_PUBLIC_SHARE_SINGLE_PASSWORD
            , [
                "password"              => $this->getL10N()->translate("Password")
                , "passwordPlaceholder" => $node->getPassword()->getPlaceholder()
                , "description"         => $this->getL10N()->translate("This password is shared with you by {$owner->getName()}.")
                , "hash"                => $publicShare->getHash()
                , "userNameLabel"       => $this->getL10N()->translate("Username")
                , "userNamePlaceholder" => $this->getL10N()->translate("Username")
                , "userNameContent"     => $node->getUsername()
                , "passwordSmall"       => $this->getL10N()->translate("Please handle the password shared with you sensitively.")
            ]
        );
        return $this->getTemplateManager()->render(PublicShareController::TEMPLATE_NAME_PUBLIC_SHARE_SINGLE_PASSWORD);
    }

    public function afterCreate(): void {

    }

}
