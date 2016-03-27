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

namespace Diamante\AutomationBundle\Infrastructure\Shared;

use Diamante\AutomationBundle\Infrastructure\Persistence\BusinessRuleRepository;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DatagridHelper
 *
 * @package Diamante\AutomationBundle\Infrastructure\Shared
 */
class DatagridHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var BusinessRuleRepository
     */
    private $businessRuleRepository;

    /** @var null|array */
    private $timeIntervalChoices = null;

    public function __construct(TranslatorInterface $translator, BusinessRuleRepository $businessRuleRepository)
    {
        $this->businessRuleRepository = $businessRuleRepository;
        $this->translator = $translator;
    }

    /**
     * @return array|null
     */
    public function getTimeIntervalChoices()
    {
        if (is_null($this->timeIntervalChoices)) {
            $options = [];
            $result = $this->businessRuleRepository->getTimeIntervalChoices();

            foreach ((array) $result as $value) {
                $type = substr($value['timeInterval'], -1);
                $time = rtrim($value['timeInterval'], 'mh');

                $message = $this->translator->transChoice(
                    sprintf('diamante.automation.cron.%s', $type),
                    $time,
                    ['%time%' => $time]
                );
                $options[$value['timeInterval']] = $message;
            }

            $this->timeIntervalChoices = $options;
        }

        return $this->timeIntervalChoices;
    }
}