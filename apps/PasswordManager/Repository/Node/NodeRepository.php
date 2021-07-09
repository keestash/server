<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\PasswordManager\Repository\Node;

use Doctrine\DBAL\Driver\ResultStatement;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\Encryption\Key\KeyService;
use KSA\GeneralApi\Repository\IOrganizationRepository;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Entity\Password\Password;
use KSA\PasswordManager\Entity\Share\Share;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\Node\NodeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IJWTService;

class NodeRepository {

    private IUserRepository         $userRepository;
    private PublicShareRepository   $publicShareRepository;
    private DateTimeService         $dateTimeService;
    private ILogger                 $logger;
    private EncryptionService       $encryptionService;
    private KeyService              $keyService;
    private IOrganizationRepository $organizationRepository;
    private IJWTService             $jwtService;
    private IBackend                $backend;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
        , PublicShareRepository $shareRepository
        , DateTimeService $dateTimeService
        , ILogger $logger
        , EncryptionService $encryptionService
        , KeyService $keyService
        , IOrganizationRepository $organizationRepository
        , IJWTService $jwtService
    ) {
        $this->userRepository         = $userRepository;
        $this->publicShareRepository  = $shareRepository;
        $this->dateTimeService        = $dateTimeService;
        $this->logger                 = $logger;
        $this->encryptionService      = $encryptionService;
        $this->keyService             = $keyService;
        $this->organizationRepository = $organizationRepository;
        $this->jwtService             = $jwtService;
        $this->backend                = $backend;
    }

    public function getRootForUser(IUser $user, int $depth = 0, int $maxDepth = PHP_INT_MAX): Root {

        $type         = Node::ROOT;
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder()
            ->select(
                [
                    'id'
                ]
            )
            ->from('pwm_node')
            ->where('user_id = ?')
            ->andWhere('type = ?')
            ->setParameter(0, $user->getId())
            ->setParameter(1, $type);
        $statement    = $queryBuilder->execute();

        if (!$statement instanceof ResultStatement) {
            $this->logger->error('error while retrieving data ' . $queryBuilder->getSQL());
            throw new PasswordManagerException();
        }
        $rows = $statement->fetchAllNumeric();
        $id   = ($rows[0] ?? [])[0] ?? null;
        if (null === $id) {
            throw new PasswordManagerException();
        }

        /** @var Root $root */
        $root = $this->getNode((int) $rows[0][0], $depth, $maxDepth);

        $root->setId((int) $rows[0][0]);
        $root->setType($type);
        return $root;
    }

    public function getByName(string $name, int $depth = 0, int $maxDepth = PHP_INT_MAX): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder()
            ->select(
                [
                    'id'
                ]
            )
            ->from('pwm_node')
            ->where('name = ?')
            ->setParameter(0, $name);

        $statement = $queryBuilder->execute();

        if (!$statement instanceof ResultStatement) {
            $this->logger->error('error while retrieving data ' . $queryBuilder->getSQL());
            return $list;
        }

        $ids = $statement->fetchAllNumeric();

        foreach ($ids as $id) {
            $list->add(
                $this->getNode((int) $id[0], $depth, $maxDepth)
            );
        }

        return $list;
    }

    public function getNode(int $id, int $depth = 0, int $maxDepth = PHP_INT_MAX): Node {

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder()
            ->select(
                [
                    'id'
                    , 'name'
                    , 'user_id'
                    , 'type'
                    , 'create_ts'
                ]
            )
            ->from('pwm_node')
            ->where('id = ?')
            ->setParameter(0, $id);
        $statement    = $queryBuilder->execute();

        if (true === is_int($statement)) {
            $log = 'error while retrieving data ' . $queryBuilder->getSQL();
            $this->logger->error($log);
            throw new PasswordManagerException($log);
        }

        $rows = $statement->fetchAllNumeric();

        if (0 === count($rows)) {
            throw new PasswordManagerException('no data found, count is 0, id is: ' . $id);
        }
        $row = $rows[0];

        $id       = $row[0];
        $name     = $row[1];
        $userId   = $row[2];
        $type     = $row[3];
        $createTs = $this->dateTimeService->fromString($row[4]);

        switch ($type) {
            case Node::CREDENTIAL:
                $node = new Credential();
                break;
            case Node::FOLDER:
                $node = new Folder();
                break;
            case Node::ROOT:
                $node = new Root();
                break;
            default:
                throw new InvalidNodeTypeException("no type for $type found for id $id");
        }

        $user = $this->userRepository->getUserById($userId);

        if (null === $user) {
            throw new PasswordManagerException();
        }

        $node->setId((int) $id);
        $node->setName((string) $name);
        $node->setUser($user);
        $node->setCreateTs($createTs);
        $node->setType((string) $type);

        if ($node instanceof Credential) {
            $node = $this->addCredentialInfo($node);
        }

        $node = $this->addOrganizationInfo($node);

        if ($node instanceof Folder) {

            if ($depth < $maxDepth) {
                $this->getEdges($node, $depth + 1, $maxDepth);
            }

        }

        return $this->addShareInfo($node);
    }

    private function addOrganizationInfo(Node $node): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'on1.`organization_id`'
            ]
        )
            ->from('`organization_node`', 'on1')
            ->where('on1.`node_id` = ?')
            ->setParameter(0, $node->getId());
        $result       = $queryBuilder->execute();

        foreach ($result->fetchAllNumeric() as $row) {
            $node->setOrganization($this->organizationRepository->get((int) $row[0]));
        }

        return $node;
    }

    private function addCredentialInfo(Credential $credential): Credential {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'node_id'
                , 'username'
                , 'password'
                , 'url'
                , 'create_ts'
                , 'note'
            ])
            ->from('`pwm_credential`')
            ->where('`node_id` = ?')
            ->setParameter(0, $credential->getId());

        $statement = $queryBuilder->execute();
        $rows      = $statement->fetchAllNumeric();
        $row       = $rows[0];

        // TODO dirty hack! as we are retrieving in a loop here
        //  and do not want to loop again later simply because
        //  of encryption, we will solve this here. Normally, this
        //  has to be at CredentialService or NodeService
        $organization = null;
        $parent       = $this->getParentNode($credential->getId(), 0, 0);
        while (null !== $parent) {
            if (null !== $parent->getOrganization()) {
                $organization = $parent->getOrganization();
                break;
            }
            $parent = $this->getParentNode($parent->getId(), 0, 0);
        }
        $keyHolder = null !== $organization ? $organization : $credential->getUser();
        $key       = $this->keyService->getKey($keyHolder);

        $credential->setCredentialId((int) $row[0]);
        $credential->setUsername(
            $this->encryptionService->decrypt(
                $key
                , (string) $row[2]
            )
        );
        $credential->setUrl(
            $this->encryptionService->decrypt(
                $key
                , (string) $row[4]
            )
        );
        $credential->setNotes(
            $this->encryptionService->decrypt(
                $key
                , (string) $row[6]
            )
        );

        $password = new Password();
        $password->setEncrypted($row[3]);
        $credential->setPassword($password);
        $credential->setCreateTs(
            $this->dateTimeService->fromFormat(
                $row[5]
            )
        );
        return $credential;
    }

    private function getEdges(Folder $folder, int $depth = 0, int $maxDepth = PHP_INT_MAX): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'e.`id`'
                , 'e.`node_id`'
                , 'e.`type`'
                , 'e.`expire_ts`'
            ]
        )
            ->from('`pwm_edge`', 'e')
            ->where('e.`parent_id` = ?')
            ->andWhere('(e.`expire_ts` is null
                and e.`type` = ?)
            or (e.`expire_ts` is not null
            and e.`type` = ?
            and e.`expire_ts` > CURRENT_TIMESTAMP)')
            ->setParameter(0, $folder->getId())
            ->setParameter(1, Edge::TYPE_REGULAR)
            ->setParameter(2, Edge::TYPE_SHARE);

        $result = $queryBuilder->execute();

        foreach ($result->fetchAllNumeric() as $row) {
            $id       = $row[0];
            $nodeId   = $row[1];
            $type     = $row[2];
            $expireTs = $row[3] !== null ?
                $this->dateTimeService->fromFormat(
                    $row[3]
                ) : null;

            $node = $this->getNode((int) $nodeId, $depth, $maxDepth);

            $edge = new Edge();
            $edge->setId((int) $id);
            $edge->setNode($node);
            $edge->setType($type);
            $edge->setExpireTs($expireTs);
            $edge->setOwner($node->getUser());
            $edge->setSharee($folder->getUser());

            $folder->addEdge($edge);
        }


    }

    private function addShareInfo(Node $node): Node {
        $sql = "
                select 
                       n.`user_id`
                       , e.`create_ts`
                       , e.`id`
                    from `pwm_edge` e
                join `pwm_node` n
                    on e.`parent_id` = n.`id`
                where e.`node_id` = " . $node->getId() . "
                    and e.`type` = '" . Edge::TYPE_SHARE . "'
                    and e.`expire_ts` is not null
                    and e.`expire_ts` >= CURRENT_TIMESTAMP
        ";

        $result = $this->backend->getConnection()->executeQuery($sql);

        foreach ($result->fetchAllNumeric() as $row) {
            $share    = new Share();
            $userId   = $row[0];
            $createTs = $row[1];
            $id       = $row[2];

            $user     = $this->userRepository->getUserById((string) $userId);
            $createTs = $this->dateTimeService->fromString($createTs);

            if (null === $user) continue;

            $user->setJWT(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_USER
                        , (string) $user->getId()
                    )
                )
            );

            $share->setId((int) $id);
            $share->setUser($user);
            $share->setCreateTs($createTs);

            $node->shareTo($share);
        }

        return $this->publicShareRepository->addShareInfo($node);
    }

    public function addRoot(Root $root): ?int {
        return $this->add($root);
    }

    public function add(Node $node): ?int {

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('pwm_node')
            ->values(
                [
                    'name'      => '?'
                    , 'user_id' => '?'
                    , 'type'    => '?'
                ]
            )
            ->setParameter(0, $node->getName())
            ->setParameter(1, $node->getUser()->getId())
            ->setParameter(2, $node->getType())
            ->execute();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) return null;
        $node->setId((int) $lastInsertId);
        return (int) $lastInsertId;
    }

    public function addFolder(Folder $folder): ?int {
        return $this->add($folder);
    }

    public function addCredential(Credential $credential): Credential {
        $nodeId = $this->add($credential);
        if (null === $nodeId) {
            throw new PasswordManagerException();
        }

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('pwm_credential')
            ->values(
                [
                    'node_id'    => '?'
                    , 'username' => '?'
                    , 'password' => '?'
                    , 'url'      => '?'
                    , 'note'     => '?'
                ]
            )
            ->setParameter(0, $nodeId)
            ->setParameter(1, $credential->getUsername())
            ->setParameter(2, $credential->getPassword()->getEncrypted())
            ->setParameter(3, $credential->getUrl())
            ->setParameter(4, $credential->getNotes())
            ->execute();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new NodeException();
        }

        $credential->setId((int) $nodeId);
        $credential->setCredentialId((int) $lastInsertId);

        return $credential;
    }

    public function exists(int $id): bool {
        return null !== $this->getNode($id, 0, 1);
    }

    public function getPathToRoot(Node $node): array {
        $sql = "
                WITH RECURSIVE descendants AS
                   (
                       SELECT e.`parent_id`                      as parent_id
                            , n.`name`                           as name
                            , n.`id`                             as node_id
                            , 1                                  as level
                            , n.`type`                           as type
                            , IF(n.`type` = 'root', true, false) as is_root
                       FROM `pwm_node` n
                                left join `pwm_edge` e on e.`node_id` = n.`id`
                       WHERE n.`id` = '" . $node->getId() . "'
                       UNION
                       DISTINCT
                       SELECT e2.`parent_id`                      as parent_id
                            , n2.`name`
                            , n2.`id`                             as node_id
                            , d.level + 1
                            , n2.`type`
                            , IF(n2.`type` = 'root', true, false) as is_root
                       FROM `descendants` d
                                left join `pwm_edge` e2 on d.`parent_id` = e2.`node_id`
                                left join `pwm_node` n2 on e2.`node_id` = n2.id
                       where n2.`id` is not null
                   )
SELECT 
       `name`
     , `node_id`
FROM `descendants` d
where d.`type` = 'folder'
ORDER BY d.`level`;
        ";

        $nodes = [];

        $result = $this->backend->getConnection()->executeQuery($sql);
        foreach ($result->fetchAllNumeric() as $row) {
            $nodes[] = [
                'name' => $row[0]
                , 'id' => (int) $row[1]
            ];
        }
        return $nodes;
    }

    public function getParentNode(int $id, int $depth = 0, int $maxDepth = PHP_INT_MAX): ?Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'parent_id'
            ]
        )
            ->from('pwm_edge')
            ->where('node_id = ?')
            ->setParameter(0, $id);

        $statement = $queryBuilder->execute();

        if (!$statement instanceof ResultStatement) {
            $this->logger->error('error while retrieving data ' . $queryBuilder->getSQL());
            return null;
        }
        $rows = $statement->fetchAllNumeric();

        if (0 === count($rows)) {
            return null;
        }

        try {
            return $this->getNode((int) $rows[0][0], $depth, $maxDepth);
        } catch (PasswordManagerException $exception) {
            return null;
        }
    }

    public function remove(Node $node): bool {
        $edgesRemoved = $this->removeEdges($node);
        if (false === $edgesRemoved) return false;

        if ($node instanceof Credential) {
            $credentialRemoved = $this->removeCredential($node);
            if (false === $credentialRemoved) return false;
        }

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_node'
            )
                ->where('id = ?')
                ->setParameter(0, $node->getId())
                ->execute() !== 0;
    }

    private function removeEdges(Node $node): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_edge'
            )
                ->where('node_id = ?')
                ->orWhere('parent_id = ?')
                ->setParameter(0, $node->getId())
                ->setParameter(1, $node->getId())
                ->execute() !== 0;
    }

    private function removeCredential(Credential $credential): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_credential'
            )
                ->where('node_id = ?')
                ->setParameter(0, $credential->getId())
                ->execute() !== 0;
    }

    public function removeEdge(string $id): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_edge'
            )
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute() !== 0;
    }

    public function updateNode(Node $node): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->update('pwm_node')
            ->set('name', '?')
            ->where('id = ?')
            ->setParameter(0, $node->getName())
            ->setParameter(1, $node->getId());
        $queryBuilder->execute();

        return $node;
    }

    public function updateCredential(Credential $credential): Credential {
        $this->updateNode($credential);
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        // TODO dirty hack! as we are retrieving in a loop here
        //  and do not want to loop again later simply because
        //  of encryption, we will solve this here. Normally, this
        //  has to be at CredentialService or NodeService
        $organization = null;
        $parent       = $this->getParentNode($credential->getId(), 0, 0);
        while (null !== $parent) {
            if (null !== $parent->getOrganization()) {
                $organization = $parent->getOrganization();
                break;
            }
            $parent = $this->getParentNode($parent->getId(), 0, 0);
        }

        $keyHolder = null !== $organization ? $organization : $credential->getUser();
        $key       = $this->keyService->getKey($keyHolder);

        $queryBuilder = $queryBuilder->update('pwm_credential')
            ->set('username', '?')
            ->set('password', '?')
            ->set('url', '?')
            ->set('note', '?')
            ->where('id = ?')
            ->setParameter(0,
                $this->encryptionService->encrypt(
                    $key
                    , $credential->getUsername()
                )
            )
            ->setParameter(1,
                $credential->getPassword()->getEncrypted()
            )
            ->setParameter(2,
                $this->encryptionService->encrypt(
                    $key
                    , (string) $credential->getUrl()
                )
            )
            ->setParameter(3,
                $this->encryptionService->encrypt(
                    $key
                    , (string) $credential->getNotes()
                )
            )
            ->setParameter(4, $credential->getCredentialId());
        $queryBuilder->execute();

        return $credential;
    }

    public function move(Node $node, Folder $parent, Folder $newParent): bool {

        $this->getEdges($parent);
        if (0 === $parent->getEdges()->size()) {
            return false;
        }

        $targetEdge = null;
        /** @var Edge $edge */
        foreach ($parent->getEdges() as $edge) {
            if ($edge->getNode()->getId() === $node->getId()) {
                $targetEdge = $edge;
                break;
            }
        }

        if (null === $targetEdge) {
            return false;
        }


        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $executed     = $queryBuilder->delete(
                'pwm_edge'
            )
                ->where('node_id = ?')
                ->andWhere('parent_id = ?')
                ->andWhere('type = ?')
                ->setParameter(0, $node->getId())
                ->setParameter(1, $parent->getId())
                ->setParameter(2, $targetEdge->getType())
                ->execute() > 0;

        if (false === $executed) return false;

        $targetEdge->setParent($newParent);
        $edge = $this->addEdge($targetEdge);
        return 0 !== $edge->getId();
    }

    public function addEdge(Edge $edge): Edge {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`pwm_edge`")
            ->values(
                [
                    "`node_id`"     => '?'
                    , "`parent_id`" => '?'
                    , "`type`"      => '?'
                    , "`expire_ts`" => '?'
                ]
            )
            ->setParameter(0, $edge->getNode()->getId())
            ->setParameter(1, $edge->getParent()->getId())
            ->setParameter(2, $edge->getType())
            ->setParameter(3,
                null !== $edge->getExpireTs()
                    ? $this->dateTimeService->toYMDHIS($edge->getExpireTs())
                    : null
            )
            ->execute();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId) || "0" === $lastInsertId) {
            throw new PasswordManagerException();
        }
        $edge->setId((int) $lastInsertId);
        return $edge;
    }

    public function removeForUser(IUser $user): bool {

        $removed = $this->publicShareRepository->removeByUser($user);
        if (false === $removed) return false;

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_edge', 'pe'
            )
                ->where('pe.`node_id` IN (
                                    SELECT DISTINCT n.`id` FROM `pwm_node` n WHERE n.`user_id` = ?
                                )')
                ->orWhere('pe.`parent_id` IN (
                                    SELECT DISTINCT n.`id` FROM `pwm_node` n WHERE n.`user_id` = ?
                                )')
                ->setParameter(0, $user->getId())
                ->setParameter(1, $user->getId())
                ->execute() !== 0;
    }

}
