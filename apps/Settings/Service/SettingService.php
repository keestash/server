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

namespace KSA\Settings\Service;

use Keestash\View\Navigation\App\Entry;
use Keestash\View\Navigation\App\Segment;
use KSP\Core\DTO\Setting\IContext;
use KSP\Core\DTO\Setting\ISetting;
use KSP\View\Navigation\App\IEntry;
use KSP\View\Navigation\App\ISegment;

class SettingService {

    /**
     * @param IContext   $context
     * @param ISetting[] $settings
     * @return ISegment
     */
    public function toSegment(IContext $context, array $settings): ISegment {
        $segment = new Segment();
        $segment->setId($context->getId());
        $segment->setTitle($context->getName());

        foreach ($settings as $setting) {
            $entry = new Entry();
            $entry->setTitle($setting->getName());
            $entry->setSelector($setting->getId());
            $entry->setIconClass(IEntry::ICON_CIRCLE);
            $segment->addEntry($entry);
        }
        return $segment;
    }

}