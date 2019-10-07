<?php

namespace Diamante\EmailProcessingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;

class HelpChannelsConfigurationType extends AbstractType
{
    const NAME = 'diamante_config_channels_help';

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
