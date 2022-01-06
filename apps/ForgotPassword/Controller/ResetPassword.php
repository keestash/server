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

namespace KSA\ForgotPassword\Controller;

use DateTime;
use Keestash\Core\Service\HTTP\HTTPService;
use KSA\ForgotPassword\Application\Application;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResetPassword extends StaticAppController {

    private TemplateRendererInterface $templateRenderer;

    public function __construct(
         IAppRenderer              $appRenderer
        , TemplateRendererInterface $templateRenderer
    ) {
        parent::__construct($appRenderer);
        $this->templateRenderer    = $templateRenderer;
    }

    public function run(ServerRequestInterface $request): string {
        $token = $request->getAttribute("token");

        return $this->templateRenderer->render(
            'forgotPassword::reset_password'
            , [
                'token' => $token
            ]
        );

    }

}
