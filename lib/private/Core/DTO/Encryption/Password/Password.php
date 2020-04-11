<?php
declare(strict_types=1);

namespace Keestash\Core\DTO\Encryption\Password;

use JsonSerializable;

class Password implements JsonSerializable {

    /** @var string $value */
    private $value;
    /** @var array $characterSet */
    private $characterSet;
    /** @var float $entropy */
    private $entropy;
    /** @var int $quality */
    private $quality;

    public function __construct() {
        $this->characterSet = [];
    }

    /**
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }

    public function getLength(): int {
        return strlen($this->getValue());
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void {
        $this->value = $value;
    }

    /**
     * @return array
     */
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
    public function getQuality(): int {
        return $this->quality;
    }

    /**
     * @param int $quality
     */
    public function setQuality(int $quality): void {
        $this->quality = $quality;
    }

    public function addCharacterSet(string $set): void {
        $this->characterSet[] = $set;
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
            "value"           => $this->getValue()
            , "character_set" => $this->getCharacterSet()
            , "entropy"       => $this->getEntropy()
            , "quality"       => $this->getQuality()
        ];
    }

}
