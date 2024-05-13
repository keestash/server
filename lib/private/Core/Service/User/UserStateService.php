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

    #[\Override] public function getState(IUser $user): IUserState {
        return $this->userStateRepository->getByUser($user);
    }

    #[\Override] public function setState(IUserState $userState): void {
        $this->userStateRepository->insert(
            $userState->getUser(),
            $userState->getState()->value,
            $userState->getStateHash()
        );
    }

    public function clear(IUser $user): void {
        $this->userStateRepository->remove($user);
    }

    public function clearCarefully(IUser $user, UserStateName $expectedState): void {
        $userState = $this->getState($user);
        if ($userState->getState() !== $expectedState) {
            throw new KeestashException();
        }
        $this->clear($user);
    }

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
