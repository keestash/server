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

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\Encryption\Password\PasswordService;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Generate implements RequestHandlerInterface {

    private PasswordService $passwordService;
    private IL10N           $translator;

    public function __construct(
        IL10N $l10n
        , PasswordService $passwordService
    ) {
        $this->passwordService = $passwordService;
        $this->translator      = $l10n;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $length       = $request->getAttribute("length");
        $upperCase    = $request->getAttribute("upperCase");
        $lowerCase    = $request->getAttribute("lowerCase");
        $digit        = $request->getAttribute("digit");
        $specialChars = $request->getAttribute("specialChars");

        $valid = $this->validParameters(
            $length
            , $upperCase
            , $lowerCase
            , $digit
            , $specialChars
        );

        if (false === $valid) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "invalid parameters"
                ]
            );
        }

        $password = $this->passwordService->generatePassword(
            (int) $length
            , $upperCase === "true"
            , $lowerCase === "true"
            , $digit === "true"
            , $specialChars === "true"
        );

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "response" => [
                    "password"  => $password
                    , "strings" => [
                        "quality" => [
                            "-1"  => $this->translator->translate("Bad")
                            , "0" => $this->translator->translate("Good")
                            , "1" => $this->translator->translate("Perfect")
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

}
