<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or
 * indirectly through a Keestash authorized reseller or distributor (a "Reseller"). Please read this EULA agreement
 * carefully before completing the installation process and using the Keestash software. It provides a license to use
 * the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Api\Generate;

use Keestash\Api\AbstractApi;

use Keestash\Core\Service\Encryption\Password\PasswordService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class Generate extends AbstractApi {

    private PasswordService $passwordService;

    public function __construct(
        IL10N $l10n
        , PasswordService $passwordService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->passwordService = $passwordService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $length       = $this->getParameter("length", null);
        $upperCase    = $this->getParameter("upperCase", null);
        $lowerCase    = $this->getParameter("lowerCase", null);
        $digit        = $this->getParameter("digit", null);
        $specialChars = $this->getParameter("specialChars", null);

        $valid = $this->validParameters(
            $length
            , $upperCase
            , $lowerCase
            , $digit
            , $specialChars
        );

        if (false === $valid) {
            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "invalid parameters"
                ]
            );
            return;
        }

        $password = $this->passwordService->generatePassword(
            (int) $length
            , $upperCase === "true"
            , $lowerCase === "true"
            , $digit === "true"
            , $specialChars === "true"
        );

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "response" => [
                    "password"  => $password
                    , "strings" => [
                        "quality" => [
                            "-1"  => $this->getL10N()->translate("Bad")
                            , "0" => $this->getL10N()->translate("Good")
                            , "1" => $this->getL10N()->translate("Perfect")
                        ]
                    ]
                ]
            ]
        );
    }

    private function validParameters(
        ?string $length
        , ?string $upperCase
        , ?string $lowerCase
        , ?string $digit
        , ?string $specialChars
    ) {
        $validOptions = [
            "true"
            , "false"
            , null
        ];

        $fields = [
            $upperCase
            , $lowerCase
            , $digit
            , $specialChars
        ];

        foreach ($fields as $field) {
            if (false === in_array($field, $validOptions)) {
                return false;
            }
        }

        if (null === $length || false === is_numeric($length)) return false;

        return true;
    }

    public function afterCreate(): void {

    }

}
