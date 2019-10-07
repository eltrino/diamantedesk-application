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

use Oro\Bundle\EmbeddedFormBundle\Twig\BackLinkExtension as BaseBackLinkExtension;

class BackLinkExtension extends BaseBackLinkExtension
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'diamante_embedded_form_back_link_extension';
    }


    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('diamante_back_link', [$this, 'backLinkFilter']),
        ];
    }


    /**
     * @param string $string
     * @param string $id
     * @return string
     */
    public function backLinkFilter($string, $id = null)
    {
        $backLinkRegexp = '/{back_link(?:\|([^}]+))?}/';
        preg_match($backLinkRegexp, $string, $matches);
        [$placeholder, $linkText] = array_pad($matches, 2, '');
        if (!$linkText) {
            $linkText = 'oro.embeddedform.back_link_default_text';
        }
        $translatedLinkText = $this->getTranslator()->trans($linkText);
        $url = $this->getRouter()->generate('diamante_embedded_form_submit', ['id' => $id]);
        $link = sprintf('<a href="%s">%s</a>', $url, $translatedLinkText);

        return str_replace($placeholder, $link, $string);
    }
}
