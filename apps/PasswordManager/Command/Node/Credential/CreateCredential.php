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
use KSP\Command\IKeestashCommand;
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

    public function __construct(
        private readonly IUserRepository     $userRepository
        , private readonly CredentialService $credentialService
        , private readonly NodeRepository    $nodeRepository
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("password-manager:credential:create")
            ->setDescription("creates a new credential");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a credential");
        $name     = $style->ask("Name") ?? "";
        $username = $style->ask("Username") ?? "";
        $password = $style->ask("Password") ?? "";
        $url      = $style->ask("URL") ?? "";
        $userId   = $style->ask("User ID") ?? "";
        $parentId = $style->ask("Parent ID") ?? "";

        $user   = $this->userRepository->getUserById($userId);
        $parent = $this->nodeRepository->getNode((int) $parentId, 0, 1);

        if (!($parent instanceof Folder)) {
            throw new PasswordManagerException();
        }

        $credential = $this->credentialService->createCredential(
            (string) $password
            , $url
            , $username
            , $name
            , $user
        );

        if ($parent->getUser()->getId() !== $user->getId()) {
            $this->writeError('parent does not belong to user', $output);
            return 1;
        }

        try {
            $this->credentialService->insertCredential(
                $credential
                , $parent
            );
        } catch (Exception $exception) {
            $this->writeError("Could not create credential $name", $output);
            $this->writeError($exception->getMessage() . " " . $exception->getTraceAsString(), $output);
            return 1;
        }
        $this->writeInfo("$name created", $output);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;

    }

}
