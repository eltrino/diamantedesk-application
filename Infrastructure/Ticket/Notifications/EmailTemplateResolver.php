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
namespace Diamante\DeskBundle\Infrastructure\Ticket\Notifications;

use Diamante\DeskBundle\Model\Ticket\Notifications\Email\TemplateResolver;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notification;

class EmailTemplateResolver implements TemplateResolver
{
    public function resolve(Notification $notification, $type = self::TYPE_TXT)
    {
        $template = null;
        if ($this->isTxtType($type)) {
            $template = 'DiamanteDeskBundle:Ticket/notification:notification.txt.twig';
        } elseif ($this->isHtmlType($type)) {
            $template = 'DiamanteDeskBundle:Ticket/notification:notification.html.twig';
        } else {
            throw new \InvalidArgumentException('Give type is invalid.');
        }

        return $template;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isTxtType($type)
    {
        return $type == self::TYPE_TXT;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isHtmlType($type)
    {
        return $type == self::TYPE_HTML;
    }
}
