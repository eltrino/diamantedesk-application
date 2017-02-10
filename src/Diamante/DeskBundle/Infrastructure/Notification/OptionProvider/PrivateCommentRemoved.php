<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Infrastructure\Notification\OptionProvider;

use Diamante\DeskBundle\Infrastructure\Notification\OptionsProvider;
use Diamante\DeskBundle\Infrastructure\Notification\OptionsProviderInterface;

class PrivateCommentRemoved extends OptionsProvider implements OptionsProviderInterface
{
    /**
     * @return string
     */
    public function getHtmlTemplate()
    {
        return '@DiamanteDesk/Automation/Notification/Entity/commentRemoved.html.twig';
    }

    /**
     * @return string
     */
    public function getTxtTemplate()
    {
        return '@DiamanteDesk/Automation/Notification/Entity/commentRemoved.html.twig';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'private_comment_removed';
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return 'diamante.desk.automation.notification.comment.private.removed';
    }

    /**
     * @param $target
     * @return array
     */
    public function getAdditionalOptions($target)
    {
        return [];
    }

    /**
     * @return array
     */
    public function getHtmlOptions()
    {
        return ['html_options' => ['content']];
    }
}
