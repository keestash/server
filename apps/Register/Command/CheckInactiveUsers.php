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

use DateInterval;
use DateTimeImmutable;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\User\UserStateName;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\User\IUserStateService;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckInactiveUsers extends KeestashCommand {

    public function __construct(
        private readonly IApiLogRepository         $apiLogRepository,
        private readonly IUserRepository           $userRepository,
        private readonly IUserStateService         $userStateService,
        private readonly LoggerInterface           $logger,
        private readonly TemplateRendererInterface $templateRenderer,
        private readonly IEmailService             $emailService,
        private readonly ICollectorService         $collectorService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("register:check-inactive-users")
            ->setDescription("checks inactive users");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $users = $this->userRepository->getAll();
        /** @var IUser $user */
        foreach ($users as $user) {
            $this->handleUser($user);
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    /*
possible cases:
    #1: user does not have any indication of usage.
        -- no log in the apilog table
        -- maybe: create ts is long time ago
    #2: user uses application regularly
        -- log entry in apilog table is not too old
    #3: user does not use application for latest apilog date < today - X month
            #3.1: no state
            #3.2: stage 1
            #3.3: stage 2
            #3.4: lock
            #3.5: delete
*/
    private function handleUser(IUser $user): void {
        $now           = new DateTimeImmutable();
        $sixMonthsAgo  = $now->sub(new DateInterval('P6M'));
        $userLogs      = $this->apiLogRepository->read($user);
        $userLogsArray = $userLogs->toArray();
        usort(
            $userLogsArray,
            static function (IAPIRequest $a, IAPIRequest $b): int {
                return (new DateTimeImmutable())->setTimestamp((int) $b->getEnd())->getTimestamp()
                    - (new DateTimeImmutable())->setTimestamp((int) $a->getEnd())->getTimestamp();
            }
        );
        /** @var IAPIRequest $latestObject */
        $latestObject = $userLogsArray[0] ?? null;

        // case #1: user does not have any indication of usage
        if (null === $latestObject) {
            $this->handleNeverLoggedIn($user);
            return;
        }

        $endDate = $now->setTimestamp((int) $latestObject->getEnd());

        // case #2: user uses application regularly
        if ($endDate >= $sixMonthsAgo) {
            return;
        }

        // case #3: user does not use application for latest apilog date < today - X month
        $state = $this->userStateService->getState($user);

        switch ($state->getState()) {
            // base case: if the user is deleted - then do nothing
            case UserStateName::REQUEST_PW_CHANGE:
            case UserStateName::DELETE:
                break;
            // case #3.1: no state - user never got notified. Go ahead with stage one
            case UserStateName::NULL:
                $this->handleStageOne($user);
                break;
            // case #3.2: stage 1 - user was already notified.
            // Check whether enough time has passed for next step (stage 2)
            case UserStateName::LOCK_CANDIDATE_STAGE_ONE:
                $this->handleStageTwo($user);
                break;
            // case #3.3: stage 2 - user was already notified for stage one.
            // Check whether enough time has passed for next step (lock)
            case UserStateName::LOCK_CANDIDATE_STAGE_TWO:
                $this->handleLock($user);
                break;
            // case #3.4: lock - user was notified two times.
            // Check whether enough time has passed for next step (delete)
            case UserStateName::LOCK:
                $this->handleDelete($user);
                break;
        }

        // 2 ways: never got an email
//        $nextState = $this->userStateService->getNextStateName($state->getState());

//        $this->sendEmail($state->getState(), $user);
//        $this->userStateService->setState(
//            new UserState(
//                0,
//                $state->getUser(),
//                $nextState,
//                new DateTimeImmutable(),
//                new DateTimeImmutable(),
//                Uuid::uuid4()->toString()
//            )
//        );

    }

    private function handleNeverLoggedIn(IUser $user): void {
        // give the user the chance to use Keestash
        // wait for 4 weeks
        $fourWeeksAgo = (new DateTimeImmutable())->sub(new DateInterval('P4W'));
        if ($user->getCreateTs() > $fourWeeksAgo) {
            return;
        }

        $this->sendEmail(
            'never-logged-in',
            'Your Keestash Account',
            [
                'title'      => 'Keestash is missing You',
                'message'    => 'You haven\'t used Keestash for a time and we wanted to check whether everything is all fine.',
                'buttonText' => 'Login',
                'buttonLink' => 'https://app.keestash.com/login',
            ],
            $user
        );
    }

    private function handleStageOne(IUser $user): void {

    }

    private function handleStageTwo(IUser $user): void {

    }

    private function handleLock(IUser $user): void {

    }

    private function handleDelete(IUser $user): void {

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
                "copyRightText" => sprintf("2022 - %s Ucar Solutions UG. All rights reserved.", (new \DateTimeImmutable())->format('Y')),
                "unsubscribe"   => "Do you no longer wish to receive e-mails? Simply reply with \"Stop\"",
            ]
        );

        $rendered = $this->templateRenderer->render(
            sprintf("marketingMail::%s", $template)
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
            ['template' => $template]
        );

//        switch ($userStateName) {
//            case UserStateName::LOCK_CANDIDATE_STAGE_ONE:
//                $template  = 'lock-candidate-stage';
//                $subject   = 'Your Keestash Account';
//                $variables = [
//                    'title'      => 'Keestash is missing You',
//                    'message'    => 'You haven\'t used Keestash for a time and we wanted to check whether everything is all fine. Are you happy with Keestash?',
//                    'buttonText' => 'Login',
//                    'buttonLink' => 'https://app.keestash.com/login',
//                ];
//                break;
//            case UserStateName::LOCK_CANDIDATE_STAGE_TWO:
//                $template  = 'lock-candidate-stage';
//                $subject   = 'Your Keestash Account is going to get locked';
//                $variables = [
//                    'title'      => 'Your Keestash Account is going to get locked',
//                    'message'    => sprintf('You did not use Keestash for a while and we want to inform you that your account is getting locked in the next %s days if you do not log in.', 3),
//                    'buttonText' => 'Login',
//                    'buttonLink' => 'https://app.keestash.com/login',
//                ];
//                break;
//            case UserStateName::LOCK:
//                $template  = 'lock';
//                $subject   = 'Your Keestash Account is locked';
//                $variables = [
//                    'title'      => 'Your Keestash Account is locked',
//                    'message'    => sprintf('Unfortunately, your Keestash account is locked and will stay for %s days locked. If you wish to use Keestash, please let us know by simply replying to this email', 3),
//                    'buttonText' => 'Activate Keestash Account',
//                ];
//                break;
//            case UserStateName::DELETE:
//                $template  = 'delete';
//                $subject   = 'Your Keestash Account is deleted';
//                $variables = [
//                    'title'      => 'Your Keestash Account is deleted',
//                    'message'    => sprintf('This is a final information about that your Keestash account will get deleted within the next %s days. This is your last chance to reactivate your account by simply replying to this email', 3),
//                    'buttonText' => 'Activate Keestash Account',
//                ];
//                break;
//            default:
//                throw new KeestashException();
//        }

    }


}
