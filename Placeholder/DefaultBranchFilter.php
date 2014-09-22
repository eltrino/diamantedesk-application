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
namespace Eltrino\DiamanteDeskBundle\Placeholder;

use Eltrino\DiamanteDeskBundle\Branch\Model\Branch;
use Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings;
use Oro\Bundle\UIBundle\Placeholder\Filter\PlaceholderFilterInterface;

class DefaultBranchFilter implements PlaceholderFilterInterface
{
    const ATTRIBUTE_NAME = 'default_branch';

    /**
     * @var \Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $systemSettings;

    public function __construct(SystemSettings $systemSettings)
    {
        $this->systemSettings = $systemSettings;
    }

    /**
     * Filter placeholder items
     *
     * @param array $items
     * @param array $variables
     * @return array
     */
    public function filter(array $items, array $variables)
    {
        $result = array();
        foreach ($items as $item) {
            if (false === isset($item[self::ATTRIBUTE_NAME])) {
                $result[] = $item;
                continue;
            }

            $attributeValue = array_values($item[self::ATTRIBUTE_NAME]);

            if (false === isset($variables[$attributeValue[0]])
                || false === ($variables[$attributeValue[0]] instanceof Branch)
            ) {
                continue;
            }

            $entity = $variables[$attributeValue[0]];
            if ($entity->getId() == $this->systemSettings->getDefaultBranchId()) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
