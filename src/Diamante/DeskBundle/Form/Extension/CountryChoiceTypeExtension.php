<?php

namespace Diamante\DeskBundle\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CountryChoiceTypeExtension extends AbstractTypeExtension
{
    const VIEW_ID = 'currency_oro_currency___default_currency_value';

    const EXCLUDED_CURRENCY_CODES = [
        'UAK'
    ];

    /** {@inheritDoc} */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    /** {@inheritDoc} */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $viewId = $view->vars['id'] ?? null;
        $choices = &$view->vars['choices'] ?? null;
        if ($viewId == self::VIEW_ID && is_array($choices)) {
            foreach ($choices as $key => $value) {
                $currencyCode = $value->value;
                if ($currencyCode && in_array($currencyCode, self::EXCLUDED_CURRENCY_CODES)) {
                    unset($choices[$key]);
                }
            }
        }
    }
}