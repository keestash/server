<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Core\Service\Controller;

use DateTime;
use Keestash\ConfigProvider;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use Keestash\Exception\KeestashException;
use Keestash\Legacy\Legacy;
use Keestash\View\Navigation\App\NavigationList;
use KSA\Login\Controller\Login;
use KSP\App\ILoader;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Router\IRouterService;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\L10N\IL10N;
use Laminas\Config\Config;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppRenderer implements IAppRenderer {

    private LockHandler               $lockHandler;
    private FileService               $fileService;
    private RawFileService            $rawFileService;
    private IFileManager              $fileManager;
    private TemplateRendererInterface $templateRenderer;
    private HTTPService               $httpService;
    private Legacy                    $legacy;
    private Config                    $config;
    private ILocaleService            $localeService;
    private IRouterService            $routerService;
    private ILoader                   $loader;
    private RouterInterface           $router;
    private IL10N                     $translator;

    public function __construct(
        IRouterService              $routerService
        , Config                    $config
        , TemplateRendererInterface $templateRenderer
        , Legacy                    $legacy
        , HTTPService               $httpService
        , LockHandler               $lockHandler
        , FileService               $fileService
        , RawFileService            $rawFileService
        , IFileManager              $fileManager
        , ILocaleService            $localeService
        , ILoader                   $loader
        , RouterInterface           $router
        , IL10N                     $translator
    ) {
        $this->lockHandler      = $lockHandler;
        $this->fileService      = $fileService;
        $this->rawFileService   = $rawFileService;
        $this->fileManager      = $fileManager;
        $this->templateRenderer = $templateRenderer;
        $this->httpService      = $httpService;
        $this->legacy           = $legacy;
        $this->config           = $config;
        $this->localeService    = $localeService;
        $this->routerService    = $routerService;
        $this->loader           = $loader;
        $this->router           = $router;
        $this->translator       = $translator;
    }

    public function renderHead(ServerRequestInterface $request): string {
        $route       = $this->routerService->getMatchedPath($request);
        $styleSheets = $this->config->get(ConfigProvider::WEB_ROUTER)
            ->get(ConfigProvider::WEB_ROUTER_STYLESHEETS)
            ->toArray();
        $scripts     = $this->config->get(ConfigProvider::WEB_ROUTER)
            ->get(ConfigProvider::WEB_ROUTER_SCRIPTS)
            ->toArray();

        return $this->templateRenderer
            ->render(
                'root::head'
                , [
                    "title"            => $this->legacy->getApplication()->get("name")
                    , "stylecss"       => $this->httpService->getBaseURL(false) . "/lib/scss/dist/style.css"
                    , "faviconPath"    => $this->httpService->getBaseURL(false) . "/asset/img/favicon.png"
                    , "fontAwesomeCss" => "https://use.fontawesome.com/releases/v5.5.0/css/all.css"
                    , "baseJs"         => $this->httpService->getBaseURL(false) . "public/js/base.bundle.js"
                    , "appStyleSheet"  => $this->httpService->getBaseURL(false, false) . 'public/css/' . ($styleSheets[$route] ?? '') . '.css'
                    , "appScript"      => $this->httpService->getBaseURL(false, false) . 'public/js/' . ($scripts[$route] ?? '') . '.bundle.js'

                ]
            );
    }

    public function renderNavBar(
        ServerRequestInterface $request
        , bool                 $static
        , bool                 $contextLess
        , bool                 $hasGlobalSearch
    ): string {
        if (true === $static) return '';

        /** @var IToken|null $token */
        $token = $request->getAttribute(IToken::class);

        $profileImage = $this->getProfileImage(
            $request->getAttribute(IUser::class)
            , $static
            , $contextLess
        );

        $routes = $this->config
            ->get(ConfigProvider::WEB_ROUTER)
            ->get(ConfigProvider::SETTINGS)
            ->toArray();

        foreach ($routes as $path => $data) {
            $routeData                 = $this->routerService->getRouteByPath($path);
            $routes[$path]['compiled'] = $this->routerService->getUri($routeData['name']);
        }

        uasort(
            $routes
            , function (array $a, array $b): int {
            return $a['order'] - $b['order'];
        }
        );

        $defaultApp = $this->loader->getDefaultApp();

        if (null === $defaultApp) {
            throw new KeestashException();
        }

        return $this->templateRenderer
            ->render(
                'root::navbar'
                , [
                    "logopath"                 => $this->httpService->getBaseURL(false) . "/asset/img/logo_no_name.png"
                    , "logoutURL"              => $this->httpService->getBaseURL() . "logout"
                    , "userImage"              => $profileImage
                    , 'contextless'            => $contextLess

                    // TODO these are added only when not public route
                    , "vendorName"             => $this->legacy->getApplication()->get("name")
                    , "settings"               => $routes
                    , "baseURL"                => $this->httpService->getBaseURL()
                    , "searchInputPlaceholder" => $this->translator->translate("Search Everything")
                    , "searchInputVisible"     => $hasGlobalSearch
                    , "mainHref"               => $this->router->generateUri(
                        $this->routerService->getRouteByPath(
                            $defaultApp->getBaseRoute()
                        )['name']
                    )
                ]
            );
    }

    private function getProfileImage(?IUser $user, bool $static, bool $contextLess): string {
        if (
            true === $static
            || true === $contextLess
            || true === $this->lockHandler->isLocked()
            || null === $user
        ) {
            return '';
        }
        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->getProfileImagePath($user)
            )
        );

        if (null === $file) {
            $file = $this->fileService->getDefaultImage();
        }

        // TODO hotfix
        //  we need to fix that fullpath stuff, where extension
        //  is sometimes part of the path and sometimes not
        $path = $file->getFullPath();
        if (false === is_file($path)) {
            $path = "{$file->getDirectory()}/{$file->getName()}";
        }
        return (string) $this->rawFileService->stringToBase64($path);
    }

    public function renderBody(
        ServerRequestInterface $request
        , bool                 $static
        , bool                 $contextLess
        , bool                 $hasAppNavigation
        , string               $appContent
        , NavigationList       $navigationList
        , IActionBar           $actionBar
        , string               $caller
        , bool                 $hasGlobalSearch
    ): string {

        return $this->templateRenderer
            ->render(
                'root::body'
                , [
                    "navigation"      => $this->renderNavBar(
                        $request
                        , $static
                        , $contextLess
                        , $hasGlobalSearch
                    )
                    , "content"       => $this->renderContent(
                        $hasAppNavigation
                        , $appContent
                        , $navigationList
                        , $static
                        , $contextLess
                        , $actionBar
                        , $caller
                    )
                    , "noContext"     => true === $static
                    , "staticContext" => true === $static
                    , "footer"        => $this->renderFooter($static)
                ]
            );
    }

    private function renderFooter(bool $static): string {
        if (true === $static) return '';

        return $this->templateRenderer
            ->render(
                'root::footer'
                , [
                    "start_year"     => $this->legacy->getApplication()->get("start_date")->format("Y")
                    , "current_year" => (new DateTime())->format("Y")
                    , "appName"      => $this->legacy->getApplication()->get("name")
                    , "vendor_name"  => $this->legacy->getVendor()->get("name")
                    , "vendor_url"   => $this->legacy->getVendor()->get("web")
                ]
            );
    }

    private function renderContent(
        bool             $hasAppNavigation
        , string         $appContent
        , NavigationList $navigationList
        , bool           $static
        , bool           $contextLess
        , IActionBar     $actionBar
        , string         $caller
    ): string {

        return $this->templateRenderer
            ->render(
                'root::content'
                , [
                    "appNavigation"      => $this->renderAppNavigation(
                        $hasAppNavigation
                        , $navigationList
                        , $static
                        , $contextLess
                        , $actionBar
                    )
                    , "appContent"       => $appContent
                    , "isLogin"          => $caller === Login::class
                    , "hasAppNavigation" => $hasAppNavigation
                    , "hasBreadcrumbs"   => false
                ]

            );
    }

    private function renderAppNavigation(
        bool             $hasAppNavigation
        , NavigationList $navigationList
        , bool           $static
        , bool           $contextLess
        , IActionBar     $actionBar
    ): string {

        return $this->templateRenderer
            ->render(
                'root::app-navigation'
                , [
                    "appNavigation"      => $navigationList->toArray(false)
                    , "hasAppNavigation" => $hasAppNavigation
                    , "actionBar"        => $this->renderActionBars(
                        $static
                        , $contextLess
                        , $actionBar
                    )
                    , "hasActionBars"    => $actionBar->hasElements()
                ]
            );
    }

    private function renderActionBars(bool $static, bool $contextLess, IActionBar $actionBar): string {
        if (true === $static || true === $contextLess) return '';
        return $this->templateRenderer->render(
            'root::actionbar'
            , [
                'actionBar' => $actionBar
            ]
        );
    }

    public function render(
        ServerRequestInterface $request
        , bool                 $hasAppNavigation
        , string               $appContent
        , bool                 $static
        , bool                 $contextLess
        , NavigationList       $navigationList
        , IActionBar           $actionBar
        , string               $caller
        , bool                 $hasGlobalSearch
    ): string {

        return $this->templateRenderer
            ->render(
                'root::html'
                , [
                    "head"        => $this->renderHead($request)
                    , "host"      => $this->httpService->getBaseURL()
                    , "apiHost"   => $this->httpService->getBaseAPIURL()
                    , "assetHost" => $this->httpService->getAssetURL()
                    , "body"      => $this->renderBody(
                        $request
                        , $static
                        , $contextLess
                        , $hasAppNavigation
                        , $appContent
                        , $navigationList
                        , $actionBar
                        , $caller
                        , $hasGlobalSearch
                    )
                    , "noContext" => true === $contextLess
                    , "language"  => $this->localeService->getLocale()
                ]
            );
    }

}