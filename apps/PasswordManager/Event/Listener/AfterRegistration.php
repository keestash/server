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

namespace KSA\PasswordManager\Event\Listener;

use DateTimeImmutable;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\DTO\MailLog\MailLog;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\System\Application;
use Keestash\Exception\FolderNotCreatedException;
use Keestash\Exception\Key\KeyNotCreatedException;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class AfterRegistration
 *
 * @package KSA\PasswordManager\Hook
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 *
 */
class AfterRegistration implements IListener {

    public const MAIL_LOG_TYPE_STARTER_EMAIL = 'email.starter.type.log.mail';

    public const FIRST_CREDENTIAL_ID = 1;
    public const ROOT_ID             = 1;

    public function __construct(
        private readonly IKeyService                 $keyService
        , private readonly LoggerInterface           $logger
        , private readonly NodeService               $nodeService
        , private readonly NodeRepository            $nodeRepository
        , private readonly CredentialService         $credentialService
        , private readonly Application               $application
        , private readonly IEmailService             $emailService
        , private readonly TemplateRendererInterface $templateRenderer
        , private readonly IL10N                     $translator
        , private readonly IMailLogRepository        $mailLogRepository
        , private readonly IDerivationRepository     $derivationRepository
        , private readonly IDerivationService        $derivationService
    ) {
    }

    /**
     * @param UserCreatedEvent $event
     */
    public function execute(IEvent $event): void {
        $this->logger->debug('start after registration', ['event' => $event::class]);
        // base case: we do not create stuff for the system user
        if ($event->getUser()->getId() === IUser::SYSTEM_USER_ID) {
            $this->logger->debug('systemUser detected. Skipping', ['user' => $event->getUser()]);
            return;
        }

        $this->logger->debug('start derivation creation for user', ['user' => $event->getUser()->getId()]);
        $this->derivationRepository->clear($event->getUser());
        $derivation = new Derivation(
            Uuid::uuid4()->toString()
            , $event->getUser()
            , $this->derivationService->derive($event->getUser()->getPassword())
            , new DateTimeImmutable()
        );
        $this->derivationRepository->add($derivation);

        $this->logger->info(
            'derivation result webhook'
            , [
                'id'         => $derivation->getId()
                , 'user'     => $derivation->getUser()
                , 'derived'  => $derivation->getDerived()
                , 'createTs' => $derivation->getCreateTs()
            ]
        );

        try {
            $this->keyService->createAndStoreKey($event->getUser());
            $this->logger->info('key created', ['userId' => $event->getUser()->getId()]);
            $root = $this->createRootFolder($event);
            $this->logger->info('root folder created', ['userId' => $event->getUser()->getId()]);
            $this->createStarterPassword($event, $root);
            $this->logger->info('password created', ['userId' => $event->getUser()->getId()]);
            $this->writeEmail($event->getUser());
            $this->logger->info('email sent', ['userId' => $event->getUser()->getId()]);
        } catch (KeyNotCreatedException $e) {
            $this->logger->error('key not created', ['exception' => $e, 'userId' => $event->getUser()->getId()]);
            $this->keyService->remove($event->getUser());
            $this->nodeRepository->removeForUser($event->getUser());
        } catch (PasswordManagerException|FolderNotCreatedException $exception) {
            $this->logger->error('password/folder not created', ['exception' => $exception, 'userId' => $event->getUser()->getId()]);
            $this->keyService->remove($event->getUser());
            $this->nodeRepository->removeForUser($event->getUser());
        }

    }

    /**
     * @param IEvent $event
     * @return Root
     * @throws FolderNotCreatedException
     */
    private function createRootFolder(IEvent $event): Root {
        $root = $this->nodeService->createRootFolder(
            AfterRegistration::ROOT_ID
            , $event->getUser()
        );

        $rootId = $this->nodeRepository->addRoot($root);

        if (null === $rootId) {
            throw new FolderNotCreatedException("could not create root folder");
        }
        return $root;
    }

    /**
     * @param IEvent $event
     * @param Root   $root
     * @return void
     * @throws PasswordManagerException
     */
    private function createStarterPassword(IEvent $event, Root $root): void {
        $credential = $this->credentialService->createCredential(
            (string) $this->application->getMetaData()->get('name')
            , (string) $this->application->getMetaData()->get("web")
            , $event->getUser()->getName()
            , (string) $this->application->getMetaData()->get("name")
            , $event->getUser()
        );
        $credential->setId(AfterRegistration::FIRST_CREDENTIAL_ID);
        $this->credentialService->insertCredential($credential, $root);
    }

    public function writeEmail(IUser $user): void {
        $this->emailService->setSubject(
            $this->translator->translate('Your Account is Created')
        );

        $this->emailService->setBody(
            $this->templateRenderer->render(
                'passwordManagerEmail::welcome_mail', [
                    'hello'       => $this->translator->translate(
                        sprintf("Hey %s,", $user->getName())
                    ),
                    'topic'       => $this->translator->translate("Your Keestash account"),
                    'content'     => $this->translator->translate("Your Keestash account is ready."),
                    'questions1'  => $this->translator->translate("In case of any questions,"),
                    'questions2'  => $this->translator->translate(" contact us here."),
                    'buttonText'  => $this->translator->translate("Start Using"),
                    'thankYou'    => $this->translator->translate("Thank you,"),
                    'teamName'    => $this->translator->translate("The Keestash Team"),
                    'currentYear' => (new DateTimeImmutable())->format('Y'),
                ]
            )
        );

        $this->emailService->addRecipient(
            sprintf("%s %s", $user->getFirstName(), $user->getLastName())
            , $user->getEmail()
        );
        $sent = $this->emailService->send();
        $this->logger->info('send register email', ['sent' => $sent]);
        $mailLog = new MailLog();
        $mailLog->setId((string) Uuid::uuid4());
        $mailLog->setSubject(AfterRegistration::MAIL_LOG_TYPE_STARTER_EMAIL);
        $mailLog->setCreateTs(new DateTimeImmutable());
        $this->mailLogRepository->insert($mailLog);
    }

}
