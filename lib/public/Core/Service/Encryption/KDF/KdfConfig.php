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

namespace KSP\Core\Service\Encryption\KDF;

/**
 * Single source of truth for the KDF (scrypt) parameters used to derive the
 * key that wraps a user's master key.
 *
 * These values are advertised to clients by /app/configuration
 * (apps/Login/Api/Configuration.php) AND used server-side to wrap master keys
 * (MasterKeyWrapService). Both MUST read from here so they can never drift —
 * if they diverge, clients can no longer unwrap keys the server wrapped.
 *
 * The wire scheme these parameters belong to is scrypt-aes-gcm-v1:
 *   derivation = scrypt(password, salt, N, r, p, dkLen)
 *   aesKey     = SHA-256(derivation)
 *   blob       = IV[12] || AES-256-GCM(masterKey) || tag[16]
 */
interface KdfConfig {

    /** scrypt CPU/memory cost. Must be a power of two. */
    public const int N = 32768;

    /** scrypt block size. */
    public const int R = 8;

    /** scrypt parallelisation. */
    public const int P = 1;

    /** Derived key length in bytes (32 = AES-256). */
    public const int DK_LEN = 32;

    /** Algorithm name advertised to clients. */
    public const string ALGORITHM = 'scrypt';

}
