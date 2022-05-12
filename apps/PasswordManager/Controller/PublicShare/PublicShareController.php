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


use DateTime;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Event\PublicShare\ControllerOpened;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSP\Core\Controller\ContextLessAppController;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Service\Controller\IAppRenderer;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PublicShareController extends ContextLessAppController {

    private TemplateRendererInterface $templateRenderer;
    private PublicShareRepository     $publicShareRepository;
    private NodeRepository            $nodeRepository;
    private IEventManager             $eventManager;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , IAppRenderer            $appRenderer
        , PublicShareRepository   $publicShareRepository
        , NodeRepository          $nodeRepository
        , IEventManager           $eventManager
    ) {
        parent::deactivateGlobalSearch();
        parent::__construct($appRenderer);

        $this->templateRenderer      = $templateRenderer;
        $this->publicShareRepository = $publicShareRepository;
        $this->nodeRepository        = $nodeRepository;
        $this->eventManager          = $eventManager;
    }

    public function run(ServerRequestInterface $request): string {

        $this->eventManager->execute(new ControllerOpened($_SERVER, new DateTime()));

        $hash  = $request->getAttribute("hash");
        $share = $this->publicShareRepository->getShare($hash);

        if (null === $share || $share->isExpired()) {
            return $this->templateRenderer->render(
                'publicShare::public_share_expired'
                , []
            );
        }

        /** @var Credential $node */
        $node = $this->nodeRepository->getNode($share->getNodeId());

        return $this->templateRenderer->render(
            'publicShare::public_share'
            , [
                'hash'   => $hash
                , 'node' => [
                    'name'       => $node->getName()
                    , 'username' => $node->getUsername()->getPlain()
                    , 'owner'    => $node->getUser()
                ]
            ]
        );

    }

}
