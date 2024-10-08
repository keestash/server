<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Keestash\Core\DTO\Encryption\Password;

use KSP\Core\DTO\Encryption\Password\IPassword;

/**
 * Class Password
 *
 * @package Keestash\Core\DTO\Encryption\Password
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Password implements IPassword {

    private string $value;
    private array  $characterSet;
    private float  $entropy;
    private int    $quality;

    public function __construct() {
        $this->characterSet = [];
    }

    #[\Override]
    public function getLength(): int {
        return strlen($this->getValue());
    }

    /**
     * @return string
     */
    #[\Override]
    public function getValue(): string {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function addCharacterSet(string $set): void {
        $this->characterSet[] = $set;
    }

    /**
     * @return array
     */
    #[\Override]
    public function getCharacterSet(): array {
        return $this->characterSet;
    }

    /**
     * @param array $characterSet
     */
    public function setCharacterSet(array $characterSet): void {
        $this->characterSet = $characterSet;
    }

    /**
     * @return float
     */
    #[\Override]
    public function getEntropy(): float {
        return $this->entropy;
    }

    /**
     * @param float $entropy
     */
    public function setEntropy(float $entropy): void {
        $this->entropy = $entropy;
    }

    /**
     * @return int
     */
    #[\Override]
    public function getQuality(): int {
        return $this->quality;
    }

    /**
     * @param int $quality
     */
    public function setQuality(int $quality): void {
        $this->quality = $quality;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\Override]
    public function jsonSerialize(): array {
        return [
            "value"           => $this->getValue()
            , "character_set" => $this->getCharacterSet()
            , "entropy"       => $this->getEntropy()
            , "quality"       => $this->getQuality()
        ];
    }


}
