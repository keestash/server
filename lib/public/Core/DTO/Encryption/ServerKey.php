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

namespace KSP\Core\DTO\Encryption;

use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\Encryption\ICredential;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;

class ServerKey implements ICredential {

    private $user           = null;
    private $baseEncryption = null;
    /** @var null|string $secret */
    private $secret               = null;
    private $encryptionRepository = null;

    public function __construct(
        IUser $user
        , IEncryptionKeyRepository $encryptionKeyRepository
    ) {
        $this->encryptionRepository = $encryptionKeyRepository;
        $this->user                 = $user;
        $this->baseEncryption       = Keestash::getServer()->getBaseEncryption($this->user);
    }

    public function getSecret(): string {
        $this->prepareKey();
        return $this->secret;
    }

    private function prepareKey(): void {
        if (null !== $this->secret) return;
        $key = $this->encryptionRepository->getKey($this->user);

        if (null === $key) {
            throw new KeyNotFoundException("could not find key for {$this->user->getId()}");
        }

        $this->secret = $this->baseEncryption->decrypt($key->getValue());
    }

}
