<?php
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
    public function backLinkFilter($string, $id)
    {
        $backLinkRegexp = '/{back_link(?:\|([^}]+))?}/';
        preg_match($backLinkRegexp, $string, $matches);
        list($placeholder, $linkText) = array_pad($matches, 2, '');
        if (!$linkText) {
            $linkText = 'oro.embeddedform.back_link_default_text';
        }
        $translatedLinkText = $this->translator->trans($linkText);
        $url = $this->router->generate('diamante_embedded_form_submit', ['id' => $id]);
        $link = sprintf('<a href="%s">%s</a>', $url, $translatedLinkText);

        return str_replace($placeholder, $link, $string);
    }
}
