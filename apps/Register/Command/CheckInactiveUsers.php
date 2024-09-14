<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace KSA\Register\Command;

use DateTimeImmutable;
use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Instance\Request\NullApiLog;
use Keestash\Core\DTO\User\NullUserState;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\DTO\User\UserStateName;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Instance\Request\ApiLogInterface;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\Router\ApiLogServiceInterface;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckInactiveUsers extends KeestashCommand {

    private OutputInterface $output;
    private bool            $dryRun = true;
    public const string OPTION_NAME_DRY_RUN = 'dry-run';
    public const string OPTION_NAME_USER_ID = 'user-id';

    public function __construct(
        private readonly IApiLogRepository         $apiLogRepository,
        private readonly IUserRepository           $userRepository,
        private readonly IUserStateService         $userStateService,
        private readonly LoggerInterface           $logger,
        private readonly TemplateRendererInterface $templateRenderer,
        private readonly IEmailService             $emailService,
        private readonly ICollectorService         $collectorService,
        private readonly ApiLogServiceInterface    $apiLogService,
        private readonly IUserService              $userService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("register:check-inactive-users")
            ->setDescription("checks inactive users")
            ->addOption(
                name: CheckInactiveUsers::OPTION_NAME_DRY_RUN,
                mode: InputOption::VALUE_NONE,
                description: 'run in dry run mode'
            )
            ->addOption(
                name: CheckInactiveUsers::OPTION_NAME_USER_ID,
                mode: InputOption::VALUE_OPTIONAL,
                description: 'the users to check'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->output = $output;
        $this->dryRun = (bool) $input->getOption(CheckInactiveUsers::OPTION_NAME_DRY_RUN);
        $userId       = $input->getOption(CheckInactiveUsers::OPTION_NAME_USER_ID);
        $users        = new ArrayList();

        if (null !== $userId) {
            $user = $this->userRepository->getUserById((string) $userId);
            $users->add($user);
        } else {
            $users = $this->userRepository->getAll();
        }

        $this->writeInfo(sprintf('in total %s users', $users->length() - 1), $this->output);
        /** @var IUser $user */
        foreach ($users as $user) {
            if ($this->userService->isSystemUser($user)) {
                continue;
            }
            $this->writeInfo(sprintf('processing user: %s', $user->getName()), $this->output);
            $this->handleUser($user);
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function handleUser(IUser $user): void {
        $userLogs      = $this->apiLogRepository->getAll();
        $userLogs      = $this->apiLogService->filterUser($user, $userLogs);
        $userLogsArray = $userLogs->toArray();
        $state         = $this->userStateService->getState($user);

        usort(
            $userLogsArray,
            static function (ApiLogInterface $a, ApiLogInterface $b): int {
                return $b->getEnd()->getTimestamp() - $a->getEnd()->getTimestamp();
            }
        );

        /** @var ApiLogInterface $latestApiLog */
        $latestApiLog  = $userLogsArray[0] ?? new NullApiLog();
        $now           = new DateTimeImmutable();
        $fourWeeksAgo  = $now->modify('-4 week');
        $referenceDate = $this->getReferenceDate($latestApiLog, $state, $user);

        if ($referenceDate >= $fourWeeksAgo) {
            $this->logger->debug('user did something in the last 4 weeks, skipping');
            $this->writeInfo('user did something in the last 4 weeks, skipping', $this->output);
            return;
        }

        switch ($state->getState()) {
            case UserStateName::NULL:
                $this->doWork(
                    [
                        'templateName' => 'never-logged-in',
                        'subject'      => 'Your Keestash Account',
                        'content'      => [
                            'title'            => 'We Noticed You Haven\'t Logged In Yet',
                            "customerName"     => $user->getName(),
                            "messagePartOne"   => "We hope this message finds you well. It seems like you have not yet logged in to your Keestash account. We are concerned that you might be missing out on the full benefits of Keestash.",
                            'messagePartTwo'   => "If there is anything preventing you from accessing your account or if you need assistance, please do not hesitate to reach out to us by simply replying to this email. We are here to help ensure you have the best experience possible.",
                            'messagePartThree' => "Your satisfaction is important to us, and we look forward to working with you.",
                            'buttonText'       => 'Start Using Keestash',
                            'buttonLink'       => 'https://app.keestash.com/login',
                        ]
                    ],
                    $state,
                    $user,
                    28
                );
                break;
            case UserStateName::NEVER_LOGGED_IN:
                $this->doWork(
                    [
                        'templateName' => 'lock-candidate-stage',
                        'subject'      => 'RE: Your Keestash Account',
                        'content'      => [
                            'title'            => 'We Haven\'t Seen You Log In Yet - Is There Anything We Can Help With?',
                            "customerName"     => $user->getName(),
                            "messagePartOne"   => "We recently reached out to let you know that you have not logged in to your Keestash account. Since we have not seen any activity yet, we wanted to check in and remind you that your account is still waiting for you.",
                            'messagePartTwo'   => "If there is anything that is holding you back from accessing your account, or if you need any help getting started, please let us know. We are here to support you in any way we can.",
                            'messagePartThree' => "Your experience matters to us, and we are eager to help you get the most out of Keestash.",
                            'buttonText'       => 'Start Using Keestash',
                            'buttonLink'       => 'https://app.keestash.com/login',
                        ]
                    ],
                    $state,
                    $user,
                    7
                );
                break;
            case UserStateName::LOCK_CANDIDATE_STAGE_ONE:
                $this->doWork(
                    [
                        'templateName' => 'lock-candidate-stage',
                        'subject'      => 'RE: RE: Your Keestash Account',
                        'content'      => [
                            'title'            => 'This is a friendly Reminder for your Keestash Account',
                            "customerName"     => $user->getName(),
                            'messagePartOne'   => 'We have noticed that despite our previous messages, you still have not used Keestash. We wanted to remind you to start using Keestash.',
                            'messagePartTwo'   => "If you are encountering any issues or have any questions, we are more than happy to assist. Your satisfaction is our top priority, and we want to make sure you have everything you need to start enjoying Keestash.",
                            'messagePartThree' => "Please do not hesitate to get in touch with us. We are here to help you every step of the way.",
                            'buttonText'       => 'Start Using Keestash',
                            'buttonLink'       => 'https://app.keestash.com/login',
                        ]
                    ],
                    $state,
                    $user,
                    7
                );
                break;
            case UserStateName::LOCK_CANDIDATE_STAGE_TWO:
                $this->doWork(
                    [
                        'templateName' => 'lock-candidate-stage',
                        'subject'      => 'RE: RE: RE: Your Keestash Account',
                        'content'      => [
                            'title'            => 'This is a final Reminder for your Keestash Account',
                            "customerName"     => $user->getName(),
                            'messagePartOne'   => 'We have noticed that despite our previous messages, you still have not used Keestash. We wanted to send one final reminder to ensure you are not missing out Keestash.',
                            'messagePartTwo'   => "If you are encountering any issues or have any questions, we are more than happy to assist. Your satisfaction is our top priority, and we want to make sure you have everything you need to start enjoying Keestash.",
                            'messagePartThree' => "Please do not hesitate to get in touch with us. We are here to help you every step of the way.",
                            'buttonText'       => 'Prevent Account Lock',
                            'buttonLink'       => 'https://app.keestash.com/login',
                        ]
                    ],
                    $state,
                    $user,
                    7
                );
                break;
            case UserStateName::LOCK:
                $this->doWork(
                    [
                        'templateName' => 'lock',
                        'subject'      => 'Important: Your Account Has Been Locked',
                        'content'      => [
                            'title'            => 'Your Account is Currently Locked - Here is What to Do Next',
                            "customerName"     => $user->getName(),
                            'messagePartOne'   => 'We wanted to inform you that your Keestash account has been locked. This action has been taken as a precautionary measure to protect your account and ensure its security.',
                            'messagePartTwo'   => "To regain access to your account, please answer to this email. We are happy to unlock your user again.",
                            'messagePartThree' => "If you believe this lock was made in error or if you need assistance with unlocking your account, please contact by simply replying to this email. We are here to help you resolve this issue as quickly as possible.",
                        ]
                    ],
                    $state,
                    $user,
                    28
                );
                break;
            case UserStateName::DELETE:
                $this->writeInfo('user is deleted, doing nothing', $this->output);
                break;
            case UserStateName::REQUEST_PW_CHANGE:
                $now         = new DateTimeImmutable();
                $passDaysAgo = $now->modify(sprintf('-%s day', 14));
                $this->writeInfo(
                    sprintf(
                        'pw change for user: %s, time passed: %s, dryrun: %s',
                        $user->getName(),
                        $state->getCreateTs() < $passDaysAgo ? 'true' : 'false',
                        $this->dryRun ? 'true' : 'false'
                    ), $this->output);
                if ($state->getCreateTs() < $passDaysAgo && false === $this->dryRun) {
                    $this->userStateService->clearCarefully($user, UserStateName::REQUEST_PW_CHANGE);
                }
                break;

        }
    }

    private function doWork(
        array      $variables,
        IUserState $state,
        IUser      $user,
        int        $daysPass
    ): void {
        // 1. check time
        $now                 = new DateTimeImmutable();
        $passDaysAgo         = $now->modify(sprintf('-%s day', $daysPass));
        $referenceDate       = $this->getReferenceDate(new NullApiLog(), $state, $user);
        $referenceDatePassed = $referenceDate > $passDaysAgo;
        $nextState           = $this->userStateService->getNextStateName($state->getState());

        $this->writeInfo(
            sprintf(
                '[ReferenceDate=%s][DaysAgo=%s][ReferenceDatePassed=%s][TemplateName=%s][CurrentState=%s][NextState=%s]',
                $referenceDate->format(DateTimeInterface::ATOM),
                $passDaysAgo->format(DateTimeInterface::ATOM),
                $referenceDatePassed ? 'Yes' : 'No',
                $variables['templateName'],
                $state->getState()->value,
                $nextState->value
            ),
            $this->output
        );

        if (true === $this->dryRun) {
            $this->writeComment('Dry Run - not doing any updates', $this->output);
            return;
        }

        if ($referenceDate > $passDaysAgo) {
            return;
        }

        // 2. send email
        $this->sendEmail(
            $variables['templateName'],
            $variables['subject'],
            $variables['content'],
            $user
        );

        // 3. change state
        $this->userStateService->setState(
            new UserState(
                0,
                $user,
                $nextState,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );
    }

    public function sendEmail(
        string $template,
        string $subject,
        array  $variables,
        IUser  $user
    ): void {

        $variables = array_merge(
            $variables,
            [
                "copyRightText" => sprintf("2022 - %s Ucar Solutions UG. All rights reserved.", (new DateTimeImmutable())->format('Y')),
                "unsubscribe"   => "Do you no longer wish to receive e-mails? Simply reply with \"Stop\"",
            ]
        );

        $rendered = $this->templateRenderer->render(
            sprintf("register::%s", $template)
            , $variables
        );

        $this->emailService->addRecipient(
            $user->getName()
            , $user->getEmail()
        );

        $this->emailService->setSubject($subject);
        $this->emailService->setBody($rendered);
        $this->emailService->send();

        $this->collectorService->addCounter(
            'inactiveusercheck',
            1,
            ['template' => str_replace('-', '_', $template)]
        );


    }

    private function getReferenceDate(ApiLogInterface $apiLog, IUserState $state, IUser $user): DateTimeInterface {
        if (false === ($apiLog instanceof NullApiLog)) {
            return $apiLog->getCreateTs();
        }
        if (false === ($state instanceof NullUserState)) {
            return $state->getCreateTs();
        }
        return $user->getCreateTs();
    }


}
