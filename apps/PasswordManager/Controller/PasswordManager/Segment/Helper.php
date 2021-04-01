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

namespace KSA\PasswordManager\Controller\PasswordManager\Segment;

use Keestash\View\Navigation\App\NavigationList;
use Keestash\View\Navigation\App\Segment;
use KSA\PasswordManager\Entity\Navigation\DefaultEntry;
use KSA\PasswordManager\Entity\Navigation\DefaultSegment;
use KSP\L10N\IL10N;
use KSP\View\Navigation\App\IEntry;

class Helper {

    private IL10N $l10n;

    public function __construct(IL10N $l10n) {
        $this->l10n = $l10n;
    }

    public function getL10n(): IL10N {
        return $this->l10n;
    }

    private function buildDefaultSegment(): Segment {
        $defaults = new DefaultSegment(
            $this->getL10N()->translate("Passwords")
        );
        $defaults->addEntry(
            new DefaultEntry(
                $this->getL10N()->translate("Home")
                , DefaultEntry::DEFAULT_ENTRY_HOME
                , null
                , IEntry::ICON_HOME
            )
        );

        $defaults->addEntry(
            new DefaultEntry(
                $this->getL10N()->translate("Recently Modified")
                , DefaultEntry::DEFAULT_ENTRY_RECENTLY_MODIFIED
                , null
                , IEntry::ICON_HISTORY
            )
        );
        $defaults->addEntry(
            new DefaultEntry(
                $this->getL10N()->translate("Shared with me")
                , DefaultEntry::DEFAULT_ENTRY_SHARED_WITH_ME
                , null
                , IEntry::ICON_SHARE
            )
        );
        return $defaults;
    }

    public function buildAppNavigation(): NavigationList {
        $navigationList = new NavigationList();


        $navigationList->add(
            $this->buildDefaultSegment()
        );

        return $navigationList;
    }

}