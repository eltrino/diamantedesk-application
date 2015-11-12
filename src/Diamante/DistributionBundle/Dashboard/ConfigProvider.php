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

namespace Diamante\DistributionBundle\Dashboard;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfigProvider extends \Oro\Bundle\DashboardBundle\Model\ConfigProvider
{
    private $disabledWidgets = ['recent_emails', 'my_calendar', 'quick_launchpad'];

    /**
     * @param array $configs
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(array $configs, EventDispatcherInterface $eventDispatcher)
    {
        $this->configs = $configs;
        $this->eventDispatcher = $eventDispatcher;

        $this->disableWidgets();

        parent::__construct($this->configs, $eventDispatcher);
    }

    private function disableWidgets()
    {
        foreach ($this->disabledWidgets as $widgetName) {
            if (array_key_exists($widgetName, $this->configs['widgets'])
                && array_key_exists('enabled', $this->configs['widgets'][$widgetName])) {

                $this->configs['widgets'][$widgetName]['enabled'] = false;
            }
        }
    }

    public static function getClass()
    {
        return __CLASS__;
    }
}