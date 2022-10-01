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

use Doctrine\DBAL\Exception;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Exception\UserNotFoundException;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Credential\Password\Entropy;
use KSA\PasswordManager\Entity\Node\Credential\Password\Password;
use KSA\PasswordManager\Entity\Node\Credential\Password\URL;
use KSA\PasswordManager\Entity\Node\Credential\Password\Username;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Entity\Share\Share;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\Node\NodeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\HTTP\IJWTService;

class NodeRepository {

    private IUserRepository         $userRepository;
    private PublicShareRepository   $publicShareRepository;
    private DateTimeService         $dateTimeService;
    private ILogger                 $logger;
    private IOrganizationRepository $organizationRepository;
    private IJWTService             $jwtService;
    private IBackend                $backend;
    private IEnvironmentService     $environmentService;

    public function __construct(
        IBackend                  $backend
        , IUserRepository         $userRepository
        , PublicShareRepository   $shareRepository
        , DateTimeService         $dateTimeService
        , ILogger                 $logger
        , IOrganizationRepository $organizationRepository
        , IJWTService             $jwtService
        , IEnvironmentService     $environmentService
    ) {
        $this->userRepository         = $userRepository;
        $this->publicShareRepository  = $shareRepository;
        $this->dateTimeService        = $dateTimeService;
        $this->logger                 = $logger;
        $this->organizationRepository = $organizationRepository;
        $this->jwtService             = $jwtService;
        $this->backend                = $backend;
        $this->environmentService     = $environmentService;
    }

    public function getRootForUser(IUser $user, int $depth = 0, int $maxDepth = PHP_INT_MAX): Root {
        $type         = Node::ROOT;
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $this->logger->debug('requesting root for user', ['user' => $user]);
        try {
            $queryBuilder->select(
                [
                    'id'
                ]
            )
                ->from('pwm_node')
                ->where('user_id = ?')
                ->andWhere('type = ?')
                ->setParameter(0, $user->getId())
                ->setParameter(1, $type);
            $statement = $queryBuilder->executeQuery();

        } catch (Exception $exception) {
            $this->logger->error(
                'error while retrieving data',
                [
                    'userId'    => $user->getId(),
                    'type'      => $type,
                    'sql'       => $queryBuilder->getSQL(),
                    'exception' => $exception
                ]
            );
            throw new PasswordManagerException('no root folder found');
        }

        $rows = $statement->fetchAllAssociative();
        $id   = $rows[0]['id'] ?? 0;
        $id   = (int) $id;
        if (0 === $id) {
            throw new PasswordManagerException();
        }

        /** @var Root $root */
        $root = $this->getNode($id, $depth, $maxDepth);

        $root->setId($id);
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

        $statement = $queryBuilder->executeQuery();

        $ids = $statement->fetchAllNumeric();

        foreach ($ids as $id) {
            $list->add(
                $this->getNode((int) $id[0], $depth, $maxDepth)
            );
        }

        return $list;
    }

    /**
     * @param int $id
     * @param int $depth
     * @param int $maxDepth
     * @return Node
     * @throws Exception
     * @throws InvalidNodeTypeException
     * @throws PasswordManagerException
     * @throws UserNotFoundException
     */
    public function getNode(int $id, int $depth = 0, int $maxDepth = PHP_INT_MAX): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder()
            ->select(
                [
                    'id'
                    , 'name'
                    , 'user_id'
                    , 'type'
                    , 'create_ts'
                    , 'update_ts'
                ]
            )
            ->from('pwm_node')
            ->where('id = ?')
            ->setParameter(0, $id);
        $statement    = $queryBuilder->executeQuery();

        $rows = $statement->fetchAllNumeric();

        if (0 === count($rows)) {
            $this->logger->warning(
                'no node found',
                [
                    'id' => $id
                ]
            );
            throw new PasswordManagerException('no node found');
        }
        $row = $rows[0];

        $id       = $row[0];
        $name     = $row[1];
        $userId   = $row[2];
        $type     = $row[3];
        $createTs = $this->dateTimeService->fromString($row[4]);
        $updateTs = null !== $row[5]
            ? $this->dateTimeService->fromString($row[5])
            : null;

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

        $user = $this->userRepository->getUserById((string) $userId);

        $node->setId((int) $id);
        $node->setName((string) $name);
        $node->setUser($user);
        $node->setCreateTs($createTs);
        $node->setUpdateTs($updateTs);
        $node->setType((string) $type);

        $node = $this->addOrganizationInfo($node);

        if ($node instanceof Credential) {
            $node = $this->addCredentialInfo($node);
        }

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
        $result       = $queryBuilder->executeQuery();

        $nodeOrganization   = null;
        $parentOrganization = null;
        foreach ($result->fetchAllNumeric() as $row) {
            $nodeOrganization = (int) $row[0];
        }

        $pathToRoot = $this->getPathToRoot($node);

        foreach ($pathToRoot as $path) {

            $organizationId = $path['organization'];

            if (null !== $organizationId) {
                $parentOrganization = $organizationId;
                break;
            }
        }

        if (null === $nodeOrganization && null === $parentOrganization) {
            return $node;
        }
        $organization = $nodeOrganization === null
            ? $parentOrganization
            : $nodeOrganization;

        $node->setOrganization($this->organizationRepository->get((int) $organization));

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
                , 'entropy'
            ])
            ->from('`pwm_credential`')
            ->where('`node_id` = ?')
            ->setParameter(0, $credential->getId());

        $statement = $queryBuilder->executeQuery();
        $rows      = $statement->fetchAllNumeric();
        $row       = $rows[0];

        $credential->setCredentialId((int) $row[0]);

        $userName = new Username();
        $userName->setEncrypted((string) $row[2]);
        $credential->setUsername($userName);

        $url = new URL();
        $url->setEncrypted((string) $row[4]);
        $credential->setUrl($url);

        $password = new Password();
        $password->setEncrypted($row[3]);
        $credential->setPassword($password);
        // TODO remove createTs on credential level, rely only on node level
        $credential->setCreateTs(
            $this->dateTimeService->fromFormat(
                $row[5]
            )
        );

        $entropy = new Entropy();
        $entropy->setEncrypted((string) $row[7]);
        $credential->setEntropy($entropy);

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
            and e.`expire_ts` > CURRENT_TIMESTAMP)
            or (e.`type` = ?)
            ')
            ->setParameter(0, $folder->getId())
            ->setParameter(1, Edge::TYPE_REGULAR)
            ->setParameter(2, Edge::TYPE_SHARE)
            ->setParameter(3, Edge::TYPE_ORGANIZATION);

        $result = $queryBuilder->executeQuery();

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

            try {
                $user = $this->userRepository->getUserById((string) $userId);
            } catch (UserNotFoundException $exception) {
                $this->logger->error('user not found', ['exception' => $exception]);
                continue;
            }
            $createTs = $this->dateTimeService->fromString($createTs);

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

    public function removeAllShares(Node $node): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $removed      = $queryBuilder->delete(
                'pwm_edge'
            )
                ->where('node_id = ?')
                ->andWhere('type = ?')
                ->setParameter(0, $node->getId())
                ->setParameter(1, Edge::TYPE_SHARE)
                ->executeStatement() !== 0;

        if (true === $removed || 0 === $node->getSharedTo()->length()) {
            $node->setSharedTo(new ArrayList());
            return $node;
        }

        throw new PasswordManagerException();
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
            ->executeStatement();

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
                    , 'entropy'  => '?'
                ]
            )
            ->setParameter(0, $nodeId)
            ->setParameter(1, $credential->getUsername()->getEncrypted())
            ->setParameter(2, $credential->getPassword()->getEncrypted())
            ->setParameter(3, $credential->getUrl()->getEncrypted())
            ->setParameter(4, $credential->getEntropy()->getEncrypted())
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new NodeException();
        }

        $credential->setId($nodeId);
        $credential->setCredentialId((int) $lastInsertId);

        return $credential;
    }

    public function exists(int $id): bool {
        try {
            $this->getNode($id, 0, 1);
        } catch (PasswordManagerException $exception) {
            return false;
        }
        return true;
    }

    public function getPathToRoot(Node $node): array {
        if ($this->environmentService->isUnitTest()) {
            // Dirty Hack!!
            // Normally, this class and especially this method
            // is overwritten in tests but somehow, the
            // DI container loads the "real" class instead of
            // the mocked one.
            // Several approaches to debug failed. Therefore, I am
            // utilising this hack.
            return [];
        }

        /**
         * @see https://www.percona.com/blog/2020/02/13/introduction-to-mysql-8-0-recursive-common-table-expression-part-2/
         * @see https://dev.mysql.com/blog-archive/a-new-simple-way-to-figure-out-why-your-recursive-cte-is-running-away/
         * @see https://dev.mysql.com/blog-archive/mysql-8-0-1-recursive-common-table-expressions-in-mysql-ctes-part-four-depth-first-or-breadth-first-traversal-transitive-closure-cycle-avoidance/
         */
        $sql = "
                WITH RECURSIVE descendants AS
                   (
                       SELECT e.`parent_id`                      as parent_id
                            , n.`name`                           as name
                            , n.`id`                             as node_id
                            , 1                                  as level
                            , n.`type`                           as type
                            , IF(n.`type` = 'root', true, false) as is_root
                            , onn.`organization_id`              as organization
                       FROM `pwm_node` n
                                left join `pwm_edge` e on e.`node_id` = n.`id`
                                left join `organization_node` onn on n.`id` = onn.`node_id`
                       WHERE n.`id` = '" . $node->getId() . "'
                       UNION
                       DISTINCT
                       SELECT e2.`parent_id`                      as parent_id
                            , n2.`name`
                            , n2.`id`                             as node_id
                            , d.level + 1
                            , n2.`type`
                            , IF(n2.`type` = 'root', true, false) as is_root
                            , onn2.`organization_id`              as organization
                       FROM `descendants` d
                                left join `pwm_edge` e2 on d.`parent_id` = e2.`node_id`
                                left join `pwm_node` n2 on e2.`node_id` = n2.id
                                left join `organization_node` onn2 on n2.`id` = onn2.`node_id`
                       where n2.`id` is not null
                   )
SELECT 
       `name`
     , `node_id`
     , `organization`
FROM `descendants` d
where d.`type` = 'folder'
ORDER BY d.`level`;
        ";

        $nodes = [];

        $result = $this->backend->getConnection()->executeQuery($sql);
        foreach ($result->fetchAllNumeric() as $row) {
            $nodes[] = [
                'name'           => $row[0]
                , 'id'           => (int) $row[1]
                , 'organization' => $row[2]
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

        $statement = $queryBuilder->executeQuery();
        $rows      = $statement->fetchAllNumeric();

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

        if ($node instanceof Root) {
            throw new PasswordManagerException();
        }

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
                ->executeStatement() !== 0;
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
                ->executeStatement() !== 0;
    }

    private function removeCredential(Credential $credential): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_credential'
            )
                ->where('node_id = ?')
                ->setParameter(0, $credential->getId())
                ->executeStatement() !== 0;
    }

    public function removeEdge(string $id): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_edge'
            )
                ->where('id = ?')
                ->setParameter(0, $id)
                ->executeStatement() !== 0;
    }

    public function removeEdgeByNodeIdAndParentId(Node $node, Node $parent): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete(
                'pwm_edge'
            )
                ->where('node_id = ?')
                ->andWhere('parent_id = ?')
                ->setParameter(0, $node->getId())
                ->setParameter(1, $parent->getId())
                ->executeStatement() !== 0;
    }

    public function updateNode(Node $node): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->update('pwm_node')
            ->set('name', '?')
            ->set('update_ts', '?')
            ->where('id = ?')
            ->setParameter(0, $node->getName())
            ->setParameter(1,
                null !== $node->getUpdateTs()
                    ? $this->dateTimeService->toYMDHIS(
                    $node->getUpdateTs()
                )
                    : null
            )
            ->setParameter(2, $node->getId());
        $queryBuilder->executeStatement();
        return $node;
    }

    public function updateCredential(Credential $credential): Credential {
        $this->backend->startTransaction();
        $this->updateNode($credential);
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->update('pwm_credential')
            ->set('username', '?')
            ->set('password', '?')
            ->set('url', '?')
            ->set('entropy', '?')
            ->where('id = ?')
            ->setParameter(0,
                $credential->getUsername()->getEncrypted()
            )
            ->setParameter(1,
                $credential->getPassword()->getEncrypted()
            )
            ->setParameter(2,
                $credential->getUrl()->getEncrypted()
            )
            ->setParameter(3,
                $credential->getEntropy()->getEncrypted()
            )
            ->setParameter(4,
                $credential->getCredentialId()
            );
        $queryBuilder->executeStatement();
        $this->backend->endTransaction();
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
                ->executeStatement() > 0;

        if (false === $executed) return false;

        $targetEdge->setParent($newParent);
        $edge = $this->addEdge($targetEdge);
        return 0 !== $edge->getId();
    }

    public function updateEdgeTypeByNodeId(Node $node, string $type): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->update('pwm_edge')
            ->set('type', '?')
            ->where('node_id = ?')
            ->setParameter(0, $type)
            ->setParameter(1, $node->getId());
        $queryBuilder->executeStatement();
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
            ->executeStatement();

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
                ->executeStatement() !== 0;
    }

}
