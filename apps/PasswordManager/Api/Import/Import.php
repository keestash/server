<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or indirectly through a Keestash authorized reseller or distributor (a "Reseller").
 * Please read this EULA agreement carefully before completing the installation process and using the Keestash software. It provides a license to use the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Api\Import;

use Keestash\Api\AbstractApi;

use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class Import extends AbstractApi {

    private $parameters = null;

    public function __construct(IL10N $l10n, ?IToken $token = null) {
        parent::__construct($l10n, $token);
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
    }

    public function create(): void {

        parent::setResponse(
            parent::createResponse(
                IResponse::RESPONSE_CODE_OK
                , [
                    "message" => "ok"
                ]
            )
        );
    }

    public function afterCreate(): void {
        // TODO: Implement afterCreate() method.
    }

}
