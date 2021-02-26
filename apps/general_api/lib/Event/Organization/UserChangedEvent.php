<?php
declare(strict_types=1);

namespace KSA\GeneralApi\Event\Organization;

use KSP\Core\DTO\Organization\IOrganization;
use Symfony\Contracts\EventDispatcher\Event;

class UserChangedEvent extends Event {

    private IOrganization $organization;

    public function __construct(IOrganization $organization) {
        $this->organization = $organization;
    }

    public function getOrganization(): IOrganization {
        return $this->organization;
    }

}