<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2026> <Dogan Ucar>
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

namespace Keestash\Core\Service\Encryption\KDF;

use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Derivation\Scrypt;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\Service\Encryption\KDF\KdfConfig;
use RuntimeException;

/**
 * Wraps and unwraps a user's master key using the scrypt-aes-gcm-v1 scheme —
 * the SINGLE server-side implementation of the format the web/mobile clients use.
 *
 * This exists so every server-side flow that mints a master key (CLI user
 * creation, etc.) produces a blob the browser can unlock. It deliberately does
 * NOT reuse the legacy AES-256-CBC+HMAC AESService, which is a different,
 * client-incompatible format.
 *
 * Scheme (must match the frontend AesService):
 *   salt       = instance_hash (global, from InstanceDB)
 *   derivation = scrypt(password, salt, N, r, p, dkLen)   // KdfConfig params
 *   aesKey     = SHA-256(derivation)
 *   blob       = IV[12] || AES-256-GCM(masterKey) || tag[16]
 *   stored value = base64(blob), kdf_version = scrypt-aes-gcm-v1
 */
final readonly class MasterKeyWrapService {

    private const int IV_LENGTH  = 12;
    private const int TAG_LENGTH = 16;
    private const string CIPHER  = 'aes-256-gcm';

    public function __construct(private InstanceDB $instanceDB) {
    }

    public function getKdfVersion(): string {
        return IKey::KDF_VERSION_SCRYPT_AES_GCM_V1;
    }

    /**
     * Derive the AES key = SHA-256(scrypt(password, instance_hash, KdfConfig...)).
     */
    public function deriveAesKey(string $password): string {
        $salt = (string) $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH);
        if ($salt === '') {
            throw new RuntimeException('instance_hash (KDF salt) is not set');
        }
        $derived = Scrypt::calc(
            $password
            , $salt
            , KdfConfig::N
            , KdfConfig::R
            , KdfConfig::P
            , KdfConfig::DK_LEN
        );
        return hash('sha256', $derived, true);
    }

    /**
     * Wrap a raw master key under the password, returning base64(IV||ct||tag).
     */
    public function wrap(string $password, string $masterKey): string {
        $aesKey = $this->deriveAesKey($password);
        $iv     = random_bytes(self::IV_LENGTH);
        $tag    = '';
        $cipher = openssl_encrypt(
            $masterKey
            , self::CIPHER
            , $aesKey
            , OPENSSL_RAW_DATA
            , $iv
            , $tag
            , ''
            , self::TAG_LENGTH
        );

        if ($cipher === false) {
            throw new RuntimeException('master key encryption failed');
        }

        return base64_encode($iv . $cipher . $tag);
    }

    /**
     * Generate a fresh random master key and wrap it. Returns base64(IV||ct||tag).
     */
    public function generateAndWrap(string $password, int $masterKeyBytes = 32): string {
        return $this->wrap($password, random_bytes($masterKeyBytes));
    }

    /**
     * Unwrap a base64(IV||ct||tag) blob back to the raw master key. Returns null
     * if the password is wrong or the blob is corrupt (GCM auth-tag failure).
     */
    public function unwrap(string $password, string $base64Blob): ?string {
        $blob = base64_decode($base64Blob, true);
        if ($blob === false || strlen($blob) < self::IV_LENGTH + self::TAG_LENGTH + 1) {
            return null;
        }

        $iv         = substr($blob, 0, self::IV_LENGTH);
        $tag        = substr($blob, -self::TAG_LENGTH);
        $ciphertext = substr($blob, self::IV_LENGTH, -self::TAG_LENGTH);
        $aesKey     = $this->deriveAesKey($password);

        $plain = openssl_decrypt(
            $ciphertext
            , self::CIPHER
            , $aesKey
            , OPENSSL_RAW_DATA
            , $iv
            , $tag
        );

        return $plain === false ? null : $plain;
    }

}
