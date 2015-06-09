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

use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Oro\Bundle\TagBundle\Entity\TagManager;

class RenderTagExtension extends \Twig_Extension
{
    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * @param TicketRepository $ticketRepository
     * @param TagManager       $tagManager
     */
    public function __construct(TicketRepository $ticketRepository, TagManager $tagManager)
    {
        $this->ticketRepository = $ticketRepository;
        $this->tagManager = $tagManager;
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
            'render_ticket_tag' => new \Twig_Function_Method(
                $this,
                'renderTag',
                array(
                    'is_safe'           => array('html'),
                    'needs_environment' => true
                )
            )
        ];
    }

    public function renderTag(\Twig_Environment $twig, $ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        $this->tagManager->loadTagging($ticket);

        return $twig->render(
            'DiamanteDeskBundle:Ticket/Datagrid/Property:tag.html.twig',
            ['tags' => $ticket->getTags()->getValues()]
        );
    }
}
