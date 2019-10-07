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
namespace Diamante\EmbeddedFormBundle\Twig;

use Oro\Bundle\EntityExtendBundle\Twig\DynamicFieldsExtension as BaseDynamicFieldsExtension;

class DynamicFieldsExtension extends BaseDynamicFieldsExtension
{

    /**
     * @param object $entity
     * @param null|string $entityClass
     * @return array
     */
    public function getFields($entity, $entityClass = null)
    {
        $dynamicRows = parent::getFields($entity, $entityClass);
        if (method_exists($entity, 'getFormType')) {
            if ($entity->getFormType() !== 'diamante_embedded_form.form_type.available_embedded_form') {
                unset($dynamicRows['branch']);
            } else {
                unset($dynamicRows['dataChannel']);
            }
        }
        return $dynamicRows;
    }
}
