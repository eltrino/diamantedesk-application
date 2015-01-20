<?php

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
        if ($entity->getFormType() != 'diamante_embedded_form.form_type.available_embedded_form') {
            unset($dynamicRows['branch']);
        }
        return $dynamicRows;
    }
}
