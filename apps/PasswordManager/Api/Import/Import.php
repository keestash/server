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

use Keestash\Api\Response\LegacyResponse;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Import implements RequestHandlerInterface {

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , []
        );
    }

}
