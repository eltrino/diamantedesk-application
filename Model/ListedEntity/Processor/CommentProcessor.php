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

namespace Diamante\AutomationBundle\Model\ListedEntity\Processor;

use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailNotification;
use Diamante\AutomationBundle\Model\Change;
use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailTemplate;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Ticket\Comment;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use \Oro\Bundle\DataAuditBundle\Entity\Repository\AuditRepository;

class CommentProcessor extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @param Entity           $entity
     * @param AbstractLogEntry $entityLog
     * @param AuditRepository  $repository
     *
     * @return array
     */
    public function getEntityChanges(Entity $entity, AbstractLogEntry $entityLog, AuditRepository $repository)
    {
        return $this->extractChanges($entityLog);
    }

    /**
     * @return array
     */
    public function getEntityEmailTemplates()
    {
        return [
            EmailTemplate::TEMPLATE_TYPE_HTML => 'DiamanteDeskBundle:Ticket/notification:notification.html.twig',
            EmailTemplate::TEMPLATE_TYPE_TXT  => 'DiamanteDeskBundle:Ticket/notification:notification.txt.twig',
        ];
    }

    /**
     * @return string
     */
    public function getEntityCreateText()
    {
        return 'Comment was added';
    }

    /**
     * @return string
     */
    public function getEntityUpdateText()
    {
        return 'Comment was updated';
    }

    /**
     * @return string
     */
    public function getEntityDeleteText()
    {
        return 'Comment was deleted';
    }

    public function getTicketEntity(Entity $entity)
    {
        /** @var Comment $comment */
        $comment = $entity;
        return $comment->getTicket();
    }

    /**
     * @param Entity $entity
     * @return string
     */
    public function formatEntityEmailSubject(Entity $entity)
    {
        /** @var Comment $comment */
        $comment = $entity;
        return sprintf('[%s] %s', $comment->getTicket()->getKey(), $comment->getTicket()->getSubject());
    }

    /**
     * @param EmailNotification $notification
     * @param string $recipientEmail
     * @return array
     */
    public function getEmailTemplateOptions(EmailNotification $notification, $recipientEmail)
    {
        $context = $notification->getContext();
        /** @var Comment $comment */
        $comment = $notification->getContext()->getTarget();

        $contentChange = new Change('content');
        foreach ($context->getTargetChangeset() as $change){
            if ($change->getFieldName() === 'content') {
                $contentChange = $change;
                break;
            }
        }

        $author = $notification->getUserService()->fetchUserDetails($comment->getAuthor());

        return [
            'delimiter' => self::EMAIL_TEMPLATE_DELIMITER,
            'header' => $this->getEntityHeader($comment, $this ,$contentChange),
            'user' => $author->getFullName(),
            'changes' => $context->getTargetChangeset(),
            'ticketKey' => $comment->getTicket()->getKey(),
            'isOroUser' => true,
            'attachments' => false
        ];
    }

}