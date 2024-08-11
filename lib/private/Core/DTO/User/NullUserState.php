<?php
declare(strict_types=1);

namespace Keestash\Core\DTO\User;

use DateTimeImmutable;

readonly class NullUserState extends UserState {

    public function __construct() {
        parent::__construct(
            0,
            new NullUser(),
            UserStateName::NULL,
            (new DateTimeImmutable())->setTimestamp(0),
            new DateTimeImmutable(),
            ''
        );
    }


}
