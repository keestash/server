<?php
declare(strict_types=1);

namespace Keestash\Core\Service\User;

use Keestash\Core\DTO\User\UserState;
use Keestash\Core\DTO\User\UserStateName;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\IUserStateService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class UserStateService implements IUserStateService {

    public function __construct(
        private IUserStateRepository $userStateRepository,
        private LoggerInterface      $logger
    ) {
    }

    #[\Override]
    public function getState(IUser $user): IUserState {
        return $this->userStateRepository->getByUser($user);
    }

    #[\Override]
    public function setState(IUserState $userState): void {
        $this->clear($userState->getUser());
        $this->userStateRepository->insert(
            $userState->getUser(),
            $userState->getState()->value,
            $userState->getStateHash()
        );
    }

    #[\Override]
    public function clear(IUser $user): void {
        $this->userStateRepository->remove($user);
    }

    #[\Override]
    public function clearCarefully(IUser $user, UserStateName $expectedState): void {
        $userState = $this->getState($user);
        if ($userState->getState() !== $expectedState) {
            throw new KeestashException();
        }
        $this->clear($user);
    }

    #[\Override]
    public function getNextStateName(UserStateName $stateName): UserStateName {
        return match ($stateName) {
            UserStateName::NULL => UserStateName::NEVER_LOGGED_IN,
            UserStateName::NEVER_LOGGED_IN => UserStateName::LOCK_CANDIDATE_STAGE_ONE,
            UserStateName::LOCK_CANDIDATE_STAGE_ONE, UserStateName::REQUEST_PW_CHANGE => UserStateName::LOCK_CANDIDATE_STAGE_TWO,
            UserStateName::LOCK_CANDIDATE_STAGE_TWO => UserStateName::LOCK,
            UserStateName::LOCK => UserStateName::DELETE,
            default => throw new KeestashException(),
        };
    }

    #[\Override]
    public function forceLock(IUser $user): void {
        $this->userStateRepository->remove($user);
//        $this->setState(
//            new UserState(
//                0,
//                $user,
//                IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_ONE,
//                new \DateTimeImmutable(),
//                new \DateTimeImmutable(),
//                Uuid::uuid4()->toString()
//            )
//        );
//        $this->setState(
//            new UserState(
//                0,
//                $user,
//                IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_TWO,
//                new \DateTimeImmutable(),
//                new \DateTimeImmutable(),
//                Uuid::uuid4()->toString()
//            )
//        );
        $this->setState(
            new UserState(
                0,
                $user,
                UserStateName::LOCK,
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );
    }

    #[\Override]
    public function forceDelete(IUser $user): void {
        $this->userStateRepository->remove($user);
//        $this->setState(
//            new UserState(
//                0,
//                $user,
//                IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_ONE,
//                new \DateTimeImmutable(),
//                new \DateTimeImmutable(),
//                Uuid::uuid4()->toString()
//            )
//        );
//        $this->setState(
//            new UserState(
//                0,
//                $user,
//                IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_TWO,
//                new \DateTimeImmutable(),
//                new \DateTimeImmutable(),
//                Uuid::uuid4()->toString()
//            )
//        );
//        $this->setState(
//            new UserState(
//                0,
//                $user,
//                IUserState::USER_STATE_LOCK,
//                new \DateTimeImmutable(),
//                new \DateTimeImmutable(),
//                Uuid::uuid4()->toString()
//            )
//        );
        $this->setState(
            new UserState(
                0,
                $user,
                UserStateName::DELETE,
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );
    }

}
