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
namespace Eltrino\DiamanteDeskBundle\Infrastructure\Ticket\Filters;

class MyReportedNewTicketsFilterUrlGenerator extends AbstractFilterUrlGenerator implements FilterUrlGeneratorInterface
{
    /**
     * @var string
     */
    private $status;

    /**
     * @param $defaultPerPage
     * @param $userId
     * @param $status
     */
    public function __construct($defaultPerPage, $userId, $status)
    {
        parent::__construct($defaultPerPage, $userId);
        $this->status = $status;
    }

    /**
     * @return mixed|string
     */
    public function generateFilterUrlPart()
    {
        return '|g/i=1&p='. $this->defaultPerPage .'&' .
        's' . $this->encodeSquareBrackets('updatedAt') . '=1&' .
        'f' . $this->encodeSquareBrackets('reporterId') . $this->encodeSquareBrackets('value') . "=$this->userId&" .
        'f' . $this->encodeSquareBrackets('reporterId') . $this->encodeSquareBrackets('type') . "=$this->textFiltertype&" .
        'f' . $this->encodeSquareBrackets('status') . $this->encodeSquareBrackets('value') . $this->encodeSquareBrackets() .
        "=$this->status&" .
        't=diamante-ticket-grid';
    }
}
