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
namespace Diamante\DeskBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;

/**
 * Class SettingsListener
 *
 * @package Diamante\DeskBundle\EventListener
 */
class SettingsListener
{
    const DIAMANTE_DEFAULT_BRANCH = 'diamante_desk___default_branch';

    /**
     * @param ConfigSettingsUpdateEvent $event
     */
    public function beforeSave(ConfigSettingsUpdateEvent $event)
    {
        $setting = $event->getSettings();

        if (array_key_exists(static::DIAMANTE_DEFAULT_BRANCH, $setting)) {
            $setting[static::DIAMANTE_DEFAULT_BRANCH]['use_parent_scope_value'] = false;
            $event->setSettings($setting);
        }
    }
}
