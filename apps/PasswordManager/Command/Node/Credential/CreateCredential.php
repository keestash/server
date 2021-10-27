<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT8 ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KSA\PasswordManager\Command\Node\Credential;

use Exception;
use Keestash\Command\KeestashCommand;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Core\Repository\User\IUserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateCredential
 *
 * @package KSA\PasswordManager\Command\Node\Credential
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class CreateCredential extends KeestashCommand {

    private IUserRepository   $userRepository;
    private CredentialService $credentialService;
    private NodeRepository    $nodeRepository;

    public function __construct(
        IUserRepository $userRepository
        , CredentialService $credentialService
        , NodeRepository $nodeRepository
    ) {
        parent::__construct();

        $this->userRepository    = $userRepository;
        $this->credentialService = $credentialService;
        $this->nodeRepository    = $nodeRepository;
    }

    protected function configure(): void {
        $this->setName("password-manager:create-credential")
            ->setDescription("creates a new credential");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a credential");
        $name     = $style->ask("Name") ?? "";
        $username = $style->ask("Username") ?? "";
        $password = $style->ask("Password") ?? "";
        $url      = $style->ask("URL") ?? "";
        $note     = $style->ask("Note") ?? "";
        $userId   = $style->ask("User ID") ?? "";
        $parentId = $style->ask("Parent ID") ?? "";

        $user   = $this->userRepository->getUserById($userId);
        $parent = $this->nodeRepository->getNode((int) $parentId, 0, 1);

        if (null === $user) {
            throw new PasswordManagerException();
        }
        
        if (!($parent instanceof Folder)) {
            throw new PasswordManagerException();
        }

        $credential = $this->credentialService->createCredential(
            $password
            , $url
            , $username
            , $name
            , $user
            , $note
        );

        if ($parent->getUser()->getId() !== $user->getId()) {
            $this->writeError('parent does not belong to user', $output);
            return;
        }

        try {
            $this->credentialService->insertCredential(
                $credential
                , $parent
            );
        } catch (Exception $exception) {
            $this->writeError("Could not create credential $name", $output);
            $this->writeError($exception->getMessage() . " " . $exception->getTraceAsString(), $output);
            return;
        }
        $this->writeInfo("$name created", $output);
        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;

    }

}
