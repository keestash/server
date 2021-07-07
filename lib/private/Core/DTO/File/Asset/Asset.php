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

namespace Keestash\Core\DTO\File\Asset;

use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\URI\URL\URL;
use KSP\Core\DTO\File\Asset\IAsset;
use KSP\Core\DTO\URI\URL\IUniformResourceLocator;

class Asset extends File implements IAsset {

    private IUniformResourceLocator $url;

    public function __construct() {
        $this->url = new URL();
        $this->url->setIdentifier('');
    }

    public function setURL(IUniformResourceLocator $url): void {
        $this->url = $url;
    }

    public function getURL(): IUniformResourceLocator {
        return $this->url;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return parent::jsonSerialize() + [
                "url" => $this->getURL()
            ];
    }

}