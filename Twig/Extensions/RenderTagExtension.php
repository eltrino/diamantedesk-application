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

namespace Diamante\DeskBundle\Twig\Extensions;

use Oro\Bundle\TagBundle\Entity\TagManager;
use Doctrine\Bundle\DoctrineBundle\Registry;

class RenderTagExtension extends \Twig_Extension
{
    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param TagManager $tagManager
     * @param Registry $registry
     */
    public function __construct(
        TagManager       $tagManager,
        Registry         $registry
    ) {
        $this->tagManager       = $tagManager;
        $this->registry         = $registry;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'diamante_tag_render_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
             new \Twig_SimpleFunction(
                'render_tag',
                [$this, 'renderTag'],
                array(
                    'is_safe'           => array('html'),
                    'needs_environment' => true
                )
            )
        ];
    }

    /**
     * Rendering tags depend on context..
     *
     * @param \Twig_Environment $twig
     * @param $entityId
     * @param $context
     * @return string
     */
    public function renderTag(\Twig_Environment $twig, $entityId, $context)
    {
        if ($context === 'branch') {
            /** @var \Diamante\DeskBundle\Entity\Branch $entity */
            $entity =  $this->registry->getRepository('DiamanteDeskBundle:Branch')->get($entityId);
        } elseif ($context === 'ticket') {
            /** @var \Diamante\DeskBundle\Entity\Ticket $entity */
            $entity = $this->registry->getRepository('DiamanteDeskBundle:Ticket')->get($entityId);
        }
        $this->tagManager->loadTagging($entity);

        return $twig->render(
            'DiamanteDeskBundle:Tag/Datagrid/Property:tag.html.twig',
            ['tags' => $entity->getTags()->getValues()]
        );
    }
}
