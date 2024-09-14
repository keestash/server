<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KST\Unit\Core\Service\File\Mime;

use Keestash\Exception\IndexOutOfBoundsException;
use Keestash\Exception\UnknownExtensionException;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\Service\File\Mime\IMimeTypeService;
use KST\Unit\TestCase;

class MimeTypeServiceTest extends TestCase {

    private IMimeTypeService $mimeTypeService;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->mimeTypeService = $this->getService(IMimeTypeService::class);
    }

    /**
     * @param string      $mime
     * @param string      $extension
     * @param string|null $exception
     * @return void
     * @throws IndexOutOfBoundsException
     * @throws UnknownExtensionException
     * @dataProvider provideGetExtension
     */
    public function testGetExtension(string $mime, string $extension, ?string $exception): void {
        if (null !== $exception) {
            $this->expectException($exception);
        }
        $result = $this->mimeTypeService->getExtension($mime);
        $this->assertTrue(true === in_array($extension, $result, true));
    }

    public static function provideGetExtension(): array {
        return [
            ['asda', '', IndexOutOfBoundsException::class]
            , ['application/xml', IExtension::XML, null]
            , ['application/acad', '', UnknownExtensionException::class]
        ];
    }

}