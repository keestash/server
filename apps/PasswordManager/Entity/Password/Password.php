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

namespace KSA\PasswordManager\Entity\Password;

use KSP\Core\DTO\Entity\IJsonObject;

class Password implements IJsonObject {

    private string  $encrypted;
    private ?string $plain;
    private int     $length = -1;

    public function getPlain(): ?string {
        return $this->plain;
    }

    public function setPlain(?string $plain): void {
        $this->plain = $plain;
    }

    public function getLength(): int {
        return $this->length;
    }

    public function setLength(int $length): void {
        $this->length = $length;
    }

    public function getEncrypted(): string {
        return $this->encrypted;
    }

    public function setEncrypted(string $encrypted): void {
        $this->encrypted = $encrypted;
    }

    public function getPlaceholder(): string {
        return str_pad('', 12, "*");
    }

    public function setPlaceholder(string $placeholder): void {
        // TODO implement
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            "placeholder" => $this->getPlaceholder()
        ];
    }

}
