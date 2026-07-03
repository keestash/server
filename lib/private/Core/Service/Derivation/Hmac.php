<?php
declare(strict_types=1);

namespace Keestash\Core\Service\Derivation;

use InvalidArgumentException;
use function function_exists;
use function hash_algos;
use function hash_hmac;
use function hash_hmac_algos;
use function in_array;
use function mb_strlen;
use function strtolower;

/**
 * PHP implementation of the RFC 2104 Hash based Message Authentication Code
 */
class Hmac {

    public const OUTPUT_STRING = false;
    public const OUTPUT_BINARY = true;

    /**
     * Last algorithm supported
     */
    protected static ?string $lastAlgorithmSupported = null;

    /**
     * Performs a HMAC computation given relevant details such as Key, Hashing
     * algorithm, the data to compute MAC of, and an output format of String,
     * or Binary.
     *
     * @throws InvalidArgumentException
     */
    public static function compute(string $key, string $hash, string $data, bool $output = self::OUTPUT_STRING): string {
        if ($key === '' || $key === '0') {
            throw new InvalidArgumentException('Provided key is null or empty');
        }

        if ($hash !== static::$lastAlgorithmSupported && !static::isSupported($hash)) {
            throw new InvalidArgumentException(
                "Hash algorithm is not supported on this PHP installation; provided '{$hash}'"
            );
        }

        return hash_hmac($hash, $data, $key, $output);
    }

    /**
     * Get the output size according to the hash algorithm and the output format
     */
    public static function getOutputSize(string $hash, bool $output = self::OUTPUT_STRING): int {
        return mb_strlen(static::compute('key', $hash, 'data', $output), '8bit');
    }

    /**
     * Get the supported algorithm
     */
    public static function getSupportedAlgorithms(): array {
        return function_exists('hash_hmac_algos') ? hash_hmac_algos() : hash_algos();
    }

    /**
     * Is the hash algorithm supported?
     */
    public static function isSupported(string $algorithm): bool {
        if ($algorithm === static::$lastAlgorithmSupported) {
            return true;
        }

        $algos = static::getSupportedAlgorithms();
        if (in_array(strtolower($algorithm), $algos, true)) {
            static::$lastAlgorithmSupported = $algorithm;
            return true;
        }

        return false;
    }

    /**
     * Clear the cache of last algorithm supported
     */
    public static function clearLastAlgorithmCache(): void {
        static::$lastAlgorithmSupported = null;
    }

}
