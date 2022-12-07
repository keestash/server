<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Service\LDAP;

use Keestash\Core\DTO\Encryption\Credential\Credential;
use Keestash\Core\DTO\LDAP\Connection;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Exception\LDAP\LDAPException;
use KSP\Core\DTO\LDAP\IConnection;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\LDAP\ILDAPService;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Ldap;

class LDAPService implements ILDAPService {

    private KeestashEncryptionService $encryptionService;
    private InstanceDB                $instanceDB;

    public function __construct(
        KeestashEncryptionService $encryptionService
        , InstanceDB              $instanceDB
    ) {
        $this->encryptionService = $encryptionService;
        $this->instanceDB        = $instanceDB;
    }

    public function listUsers(IConnection $connection): array {
        $ldap = Ldap::create(
            'ext_ldap',
            [
                'host'   => $connection->getHost()
                , 'port' => (int) $connection->getPort()
            ]
        );

        $decryptedConnection = $this->decryptLdapConnection($connection);

        $ldap->bind(
            $decryptedConnection->getUserDn()
            , $decryptedConnection->getPassword()
        );
        $query = $ldap->query(
            $decryptedConnection->getBaseDn()
            , '(&(objectclass=person))'
        );
        return $query->execute()->toArray();
    }

    public function decryptLdapConnection(IConnection $connection): IConnection {
        $instanceHash = $this->instanceDB->getOption(
            InstanceDB::OPTION_NAME_INSTANCE_HASH
        );

        if (null === $instanceHash) {
            throw new LDAPException();
        };
        $c = new Credential();
        $c->setSecret($instanceHash);
        return new Connection(
            $connection->getHost()
            , $connection->getPort()
            , $this->encryptionService->decrypt(
            $c
            , base64_decode(
                $connection->getUserDn()
            )
        )
            , $this->encryptionService->decrypt(
            $c
            , base64_decode(
                $connection->getPassword()
            )
        )
            , $this->encryptionService->decrypt(
            $c
            , base64_decode(
                $connection->getBaseDn()
            )
        )
            , $connection->getActiveTs()
            , $connection->getCreateTs()
        );
    }

    public function verifyUser(
        IUser         $user
        , IConnection $connection
        , string      $plainPassword
    ): bool {

        if ("" === $plainPassword) {
            return false;
        }

        try {
            $decryptedConnection = $this->decryptLdapConnection($connection);
            $ldap                = Ldap::create(
                'ext_ldap',
                [
                    'host'   => $connection->getHost()
                    , 'port' => (int) $connection->getPort()
                ]
            );
            $ldap->bind('uid=' . $user->getName() . ',' . $decryptedConnection->getBaseDn(), $plainPassword);
            return true;
        } catch (ConnectionException $exception) {
            return false;
        }
    }

}