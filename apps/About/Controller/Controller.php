<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KSA\About\Controller;

use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Legacy\Legacy;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends FullscreenAppController {

    private Legacy                    $legacy;
    private TemplateRendererInterface $renderer;
    private IL10N                     $translator;
    private HTTPService               $httpService;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , HTTPService $httpService
        , Legacy $legacy
        , IL10N $translator
        , IAppRenderer $appRenderer
    ) {
        $this->legacy      = $legacy;
        $this->renderer    = $templateRenderer;
        $this->translator  = $translator;
        $this->httpService = $httpService;

        parent::__construct($appRenderer);
    }

    public function run(ServerRequestInterface $request): string {
        return $this->renderer->render(
            'about::about'
            , $this->getData()
        );

    }

    private function getData(): array {
        return [
            "image"        => [
                "logoPath"  => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "altName" => $this->legacy->getApplication()->get("name")
            ]
            , "product"    => [
                "headline"       => $this->translator->translate("Open Source Password Manager")
                , "description"  => $this->translator->translate("Keestash stores your password encrypted and secure. Easily install on a server in your company, at home or let us host it for you!")
                , "global"       => $this->translator->translate("on-premise or cloud subscription")
                , "openStandard" => $this->translator->translate("Open Standards and Legacy Integration")
                , "openSource"   => $this->translator->translate("100% Open Source")
            ]
            , "interested" => [
                "headline"      => $this->translator->translate("Interested?")
                , "description" => $this->translator->translate("We easily integrate Keestash in your existing IT infrastructure or set up a new environment - on-premise or cloud based, hosted by us!")
                , "letstalk"    => [
                    "headline"  => $this->translator->translate("Let's talk!")
                    , "website" => [
                        "href"   => $this->legacy->getApplication()->get("web")
                        , "name" => $this->legacy->getApplication()->get("name") . ".com"
                    ]
                    , "phone"   => [
                        "href"   => $this->legacy->getApplication()->get("phone")
                        , "name" => $this->legacy->getApplication()->get("phone")
                    ]
                    , "email"   => [
                        "href"   => $this->legacy->getApplication()->get("email")
                        , "name" => $this->legacy->getApplication()->get("email")
                    ]
                ]
                , "socialmedia" => [
                    "headline"   => $this->translator->translate("Social Media")
                    , "twitter"  => [
                        "href"   => $this->legacy->getApplication()->get("twitterPage")
                        , "name" => str_replace("https://www.", "", $this->legacy->getApplication()->get("twitterPage"))
                    ]
                    , "facebook" => [
                        "href"   => $this->legacy->getApplication()->get("facebookPage")
                        , "name" => str_replace("https://www.", "", $this->legacy->getApplication()->get("facebookPage"))
                    ]
                    , "linkedin" => [
                        "href"   => $this->legacy->getApplication()->get("linkedInPage")
                        , "name" => str_replace("https://www.", "", $this->legacy->getApplication()->get("linkedInPage"))
                    ]
                ]
            ]
            , "whitepaper" => [
                "headline"             => $this->translator->translate("Keestash Whitepaper")
                , "description"        => $this->translator->translate("Download our whitepaper describing our product and services.")
                , "productAndServices" => [
                    "english"  => [
                        "link"       => ""
                        , "name"     => "An Open Source Enterprise Platform For Secure Password Management"
                        , "language" => "English Version"
                        , "version"  => "1.0.0"
                    ]
                    , "german" => [
                        "link"       => ""
                        , "name"     => "Eine Open-Source-Unternehmensplattform für die sichere Passwortverwaltung"
                        , "language" => "English Version"
                        , "version"  => "1.0.0"
                    ]
                ]
            ]
        ];
    }

}
