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

use Keestash;
use Keestash\Legacy\Legacy;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class Controller extends FullscreenAppController {

    public const TEMPLATE_NAME_ABOUT = "about.twig";

    private Legacy $legacy;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , Legacy $legacy
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );

        $this->legacy = $legacy;
    }

    public function onCreate(): void {

    }

    public function create(): void {
        $this->getTemplateManager()
            ->replace(
                Controller::TEMPLATE_NAME_ABOUT
                , [
                    "image"        => [
                        "logoPath"  => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                        , "altName" => $this->legacy->getApplication()->get("name")
                    ]
                    , "product"    => [
                        "headline"       => $this->getL10N()->translate("Open Source Password Manager")
                        , "description"  => $this->getL10N()->translate("Keestash stores your password encrypted and secure. Easily install on a server in your company, at home or let us host it for you!")
                        , "global"       => $this->getL10N()->translate("Host wherever you decide - on-premise or cloud based")
                        , "openStandard" => $this->getL10N()->translate("Open Standards and Legacy Integration")
                        , "openSource"   => $this->getL10N()->translate("100% Open Source")
                    ]
                    , "interested" => [
                        "headline"      => $this->getL10N()->translate("Interested?")
                        , "description" => $this->getL10N()->translate("We easily integrate Keestash in your existing IT infrastructure or set up a new environment - on-premise or cloud based, hosted by us!")
                        , "letstalk"    => [
                            "headline"  => $this->getL10N()->translate("Let's talk!")
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
                            "headline"   => $this->getL10N()->translate("Follow us on Social Media")
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
                        "headline"             => $this->getL10N()->translate("Keestash Whitepaper")
                        , "description"        => $this->getL10N()->translate("Download our whitepaper describing our product and services.")
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
                ]
            );
        $this->setAppContent(
            $this->getTemplateManager()
                ->render(Controller::TEMPLATE_NAME_ABOUT)
        );
    }

    public function afterCreate(): void {

    }

}
