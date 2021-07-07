<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or
 * indirectly through a Keestash authorized reseller or distributor (a "Reseller"). Please read this EULA agreement
 * carefully before completing the installation process and using the Keestash software. It provides a license to use
 * the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Repository;

use doganoo\DIP\DateTime\DateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Repository\User\UserRepository;
use KSA\PasswordManager\Entity\Comment\Comment;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Exception\Node\Comment\CommentRepositoryException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\HTTP\IJWTService;

class CommentRepository {

    private NodeRepository  $nodeRepository;
    private UserRepository  $userRepository;
    private DateTimeService $dateTimeService;
    private IJWTService     $jwtService;
    private IBackend        $backend;

    public function __construct(
        IBackend $backend
        , NodeRepository $nodeRepository
        , UserRepository $userRepository
        , DateTimeService $dateTimeService
        , IJWTService $jwtService
    ) {
        $this->nodeRepository  = $nodeRepository;
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
        $this->jwtService      = $jwtService;
        $this->backend         = $backend;
    }

    /**
     * @param Comment $comment
     *
     * @return Comment
     * @throws CommentRepositoryException
     */
    public function addComment(Comment $comment): Comment {

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('pwm_comment')
            ->values(
                [
                    'comment'     => '?'
                    , 'node_id'   => '?'
                    , 'create_ts' => '?'
                    , 'user_id'   => '?'
                ]
            )
            ->setParameter(0, $comment->getComment())
            ->setParameter(1, $comment->getNode()->getId())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS(
                    $comment->getCreateTs()
                )
            )
            ->setParameter(3, $comment->getUser()->getId())
            ->execute();

        $id = $this->backend->getConnection()->lastInsertId();
        if (false === is_numeric($id)) {
            throw new CommentRepositoryException();
        }

        $comment->setId((int) $id);
        return $comment;
    }

    public function getCommentsByNode(Node $node): ?ArrayList {
        $list = new ArrayList();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $comments     = $queryBuilder
            ->select(
                [
                    'id'
                    , 'comment'
                    , 'node_id'
                    , 'user_id'
                    , 'create_ts'
                ]
            )
            ->from('pwm_comment')
            ->where('node_id = ?')
            ->setParameter(0, $node->getId())
            ->execute()
            ->fetchAll();

        foreach ($comments as $row) {

            $id            = $row["id"];
            $commentString = $row["comment"];
            $nodeId        = $row["node_id"];
            $userId        = $row["user_id"];
            $createTs      = $row["create_ts"];

            $node = $this->nodeRepository->getNode((int) $nodeId, 0, 1);
            $user = $this->userRepository->getUserById((string) $userId);

            if (null === $user) {
                throw new PasswordManagerException();
            }

            $comment = new Comment();
            $comment->setId((int) $id);
            $comment->setComment($commentString);
            $comment->setNode($node);
            $comment->setUser($user);
            $comment->setJWT(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_ASSET
                        , 'default'
                    )
                )
            );
            $comment->setCreateTs(
                $this->dateTimeService->fromFormat($createTs)
            );

            $list->add($comment);
        }

        return $list;
    }

    public function remove(int $id): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('pwm_comment')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute() !== 0;
    }

    public function removeForUser(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('pwm_comment')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->execute() !== 0;
    }

}
