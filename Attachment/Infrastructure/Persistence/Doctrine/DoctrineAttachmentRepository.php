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

namespace Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\Persistence\Doctrine;

use Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment;

class DoctrineAttachmentRepository extends \Doctrine\ORM\EntityRepository
    implements \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentRepository
{
    /**
     * Retrieves attachment
     * @param $id
     * @return Attachment
     */
    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * Save attachment
     * @param Attachment $attachment
     * @return void
     */
    public function store(Attachment $attachment)
    {
        $this->getEntityManager()->persist($attachment);
        $this->getEntityManager()->flush();
    }

    /**
     * Remove attachment
     * @param Attachment $attachment
     * @return void
     */
    public function remove(Attachment $attachment)
    {
        $this->getEntityManager()->remove($attachment);
        $this->getEntityManager()->flush();
    }
}
