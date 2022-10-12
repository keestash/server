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

namespace KST\Integration\Core\Service\File\RawFile;

use Keestash\Exception\File\FileNotExistsException;
use Keestash\Exception\File\FileNotFoundException;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\Service\File\RawFile\IRawFileService;
use KST\TestCase;

class RawFileServiceTest extends TestCase {

    private IRawFileService $rawFileService;

    protected function setUp(): void {
        parent::setUp();
        $this->rawFileService = $this->getService(IRawFileService::class);
    }

    public function testGetMimeType(): void {
        $mimeType = $this->rawFileService->getMimeType(__DIR__ . '/rawfileservicetestfile.txt');
        $this->assertTrue($mimeType === 'text/plain');
    }

    public function testGetFileExtensions(): void {
        $extensions = $this->rawFileService->getFileExtensions(__DIR__ . '/rawfileservicetestfile.txt');
        $this->assertTrue(true === in_array(IExtension::TEXT, $extensions, true));
    }

    /**
     * @return void
     * @throws FileNotFoundException
     * @dataProvider provideStringToUri
     */
    public function testStringToUri(string $path, string $result, bool $strict, ?string $exception): void {
        if (null !== $exception) {
            $this->expectException($exception);
        }
        $uri = $this->rawFileService->stringToUri($path, $strict);
        $this->assertTrue($uri->getIdentifier() === $result);
    }

    public function testStringToBase64(): void {
        $base64 = $this->rawFileService->stringToBase64(__DIR__ . '/rawfileservicetestfile.txt');
        $this->assertIsString($base64);
    }

    public function provideStringToUri(): array {
        return [
            [__DIR__, __DIR__, true, null]
            , [__DIR__ . '/dev/null', __DIR__, true, FileNotExistsException::class]
            , [__DIR__ . '/dev/null', __DIR__ . '/dev/null', false, null]
        ];
    }

}