<?php
declare(strict_types=1);

namespace KSA\PasswordManager\Entity\Share;

readonly class NullShare extends PublicShare {

    public function __construct() {
        parent::__construct(0, 0, '', new \DateTime('1970-01-01'), '', '');
    }

}
