<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\DeskBundle\Infrastructure\Persistence;

use Diamante\DeskBundle\Model\Ticket\CommentRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Diamante\DeskBundle\Model\User\User as UserModel;

class DoctrineCommentRepository extends DoctrineGenericRepository implements CommentRepository
{
    /**
     * Remove author id from comment table
     * @param User $user
     */
    public function removeCommentAuthor(User $user)
    {
        $query = $this->_em
            ->createQuery("UPDATE DiamanteDeskBundle:Comment t SET t.author = null WHERE t.author = :author_id");
        $query->setParameters(array(
                'author_id' => UserModel::TYPE_ORO . UserModel::DELIMITER . $user->getId(),
            ));
        $query->execute();
    }
}
