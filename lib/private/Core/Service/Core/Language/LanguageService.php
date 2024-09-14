<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Service\Core\Language;

use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use Locale;

class LanguageService implements ILanguageService {

    public function __construct(private readonly ILocaleService $localeService)
    {
    }

    #[\Override]
    public function getLanguage(): string {
        return Locale::getDisplayLanguage(
            $this->localeService->getLocale()
        );
    }

    #[\Override]
    public function getLanguageForUser(IUser $user): string {
        return Locale::getDisplayLanguage(
            $this->localeService->getLocaleForUser($user)
        );
    }


}
