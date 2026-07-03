<?php
declare(strict_types=1);

namespace Keestash\Core\Service\Derivation;

use InvalidArgumentException;
use function hash_pbkdf2;
use function in_array;
use function hash_algos;

/**
 * PBKDF2 (Password-Based Key Derivation Function 2)
 *
 * @see https://tools.ietf.org/html/rfc2898
 */
abstract class Pbkdf2
{
    /**
     * Execute the PBKDF2 algorithm
     *
     * @param  string $hash   The hash algorithm to use (e.g. 'sha256')
     * @param  string $password  The source password / passphrase
     * @param  string $salt      The salt value
     * @param  int    $iterations  Iteration count
     * @param  int    $length      Length of the derived key in bytes
     * @return string             The derived key (raw binary)
     * @throws InvalidArgumentException
     */
    public static function calc(string $hash, string $password, string $salt, int $iterations, int $length): string
    {
        if (!in_array($hash, hash_algos(), true)) {
            throw new InvalidArgumentException(
                "Hash algorithm '{$hash}' is not supported on this PHP installation"
            );
        }

        if ($iterations <= 0) {
            throw new InvalidArgumentException('Iterations must be a positive integer');
        }

        if ($length <= 0) {
            throw new InvalidArgumentException('Length must be a positive integer');
        }

        return hash_pbkdf2($hash, $password, $salt, $iterations, $length, true);
    }
}
