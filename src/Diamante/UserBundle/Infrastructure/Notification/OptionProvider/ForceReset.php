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

namespace Diamante\UserBundle\Infrastructure\Notification\OptionProvider;


use Diamante\DeskBundle\Infrastructure\Notification\OptionsProviderInterface;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Infrastructure\Notification\OptionsProvider;

class ForceReset extends OptionsProvider implements OptionsProviderInterface
{
    const HTML_TEMPLATE = 'DiamanteUserBundle:Notification:ForceReset/reset.html.twig';
    const TXT_TEMPLATE  = 'DiamanteUserBundle:Notification:ForceReset/reset.txt.twig';

    const SUBJECT_IDENTIFIER = 'diamante.user.notification.force_reset';

    /**
     * @return string
     */
    public function getName()
    {
        return 'force_reset';
    }

    /**
     * @return string
     */
    public function getHtmlTemplate()
    {
        return self::HTML_TEMPLATE;
    }

    /**
     * @return string
     */
    public function getTxtTemplate()
    {
        return self::TXT_TEMPLATE;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return self::SUBJECT_IDENTIFIER;
    }

    /**
     * @return array
     */
    public function getRequiredParams()
    {
        return array_merge(parent::getRequiredParams(), ['activation_hash']);
    }

    /**
     * @return mixed
     */
    public function getDefaultOptions()
    {
        return [];
    }

    /**
     * @param $target
     * @return array
     */
    public function getAdditionalOptions($target)
    {
        return [];
    }
}