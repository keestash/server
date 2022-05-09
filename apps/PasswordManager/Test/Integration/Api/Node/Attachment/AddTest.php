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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Attachment;

use KSA\PasswordManager\Api\Node\Attachment\Add;
use KSA\PasswordManager\Exception\Node\Credential\CredentialException;
use KSA\PasswordManager\Exception\Node\Credential\NoFileException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KST\TestCase;
use Laminas\Diactoros\UploadedFile;

class AddTest extends TestCase {

    public function testAddFile(): void {
        /** @var Add $add */
        $add = $this->getServiceManager()->get(Add::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        $userRoot       = $nodeRepository->getRootForUser($user);

        $node = $credentialService->createCredential(
            "addAttachmentPassword"
            , "keestash.test"
            , "add.attachment.test"
            , "AddAttachmentTest"
            , $user
        );
        $edge = $credentialService->insertCredential($node, $userRoot);

        $file = tempnam(sys_get_temp_dir(), '');
        file_put_contents((string) $file, "integrationtest");

        $uploadedFile = new UploadedFile(
            (string) $file
            , 0
            , 0
        );

        $request  = $this->getDefaultRequest(
            [
                'node_id' => $edge->getNode()->getId()
            ]
        );
        $request  = $request->withUploadedFiles([$uploadedFile]);
        $response = $add->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        unlink((string) $file);
    }

    public function testWithNoFiles(): void {
        /** @var Add $add */
        $add = $this->getServiceManager()->get(Add::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        $userRoot       = $nodeRepository->getRootForUser($user);

        $node = $credentialService->createCredential(
            "addAttachmentNoFilesPassword"
            , "keestash.test"
            , "add.attachment.no.files.test"
            , "AddAttachmentTest"
            , $user
        );
        $edge = $credentialService->insertCredential($node, $userRoot);

        $request  = $this->getDefaultRequest(
            [
                'node_id' => $edge->getNode()->getId()
            ]
        );
        $response = $add->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNoFilesAndNoId(): void {
        /** @var Add $add */
        $add      = $this->getServiceManager()->get(Add::class);
        $request  = $this->getDefaultRequest();
        $response = $add->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testAddFileFilesOnly(): void {
        /** @var Add $add */
        $add = $this->getServiceManager()->get(Add::class);

        $this->expectException(CredentialException::class);
        $file = (string) tempnam(sys_get_temp_dir(), '');
        file_put_contents($file, "integrationtest.testAddFileFilesOnly");

        $uploadedFile = new UploadedFile(
            $file
            , filesize($file)
            , 0
        );

        $request  = $this->getDefaultRequest([
            "node_id" => "1"
        ]);
        $request  = $request->withUploadedFiles([$uploadedFile]);
        $response = $add->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        unlink($file);
    }

    public function testAddFileInvalidNode(): void {
        /** @var Add $add */
        $add = $this->getServiceManager()->get(Add::class);

        $file = (string) tempnam(sys_get_temp_dir(), '');
        file_put_contents($file, "integrationtest.testAddFileInvalidNode");

        $this->expectException(CredentialException::class);

        $uploadedFile = new UploadedFile(
            $file
            , 0
            , 0
        );

        $request  = $this->getDefaultRequest(
            [
                'node_id' => 9999
            ]
        );
        $request  = $request->withUploadedFiles([$uploadedFile]);
        $response = $add->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        unlink($file);
    }


}