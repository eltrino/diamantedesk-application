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
namespace Diamante\DeskBundle\Infrastructure\Ticket\Filters;

class MyReportedNewTicketsFilterUrlGenerator extends AbstractFilterUrlGenerator
    implements FilterUrlGeneratorInterface
{
    /**
     * @var string
     */
    private $status;

    /**
     * @param $defaultPerPage
     * @param $userFullName
     * @param $status
     */
    public function __construct($defaultPerPage, $userFullName, $status)
    {
        parent::__construct($defaultPerPage, $userFullName);
        $this->status = $status;
    }

    /**
     * @return mixed|string
     */
    public function generateFilterUrlPart()
    {
        $params = [
            'i' => 1,
            'p' => $this->defaultPerPage,
            's' => [
                'updatedAt' => 1
            ],
            'f' => [
                'reporterFullName' => [
                    'value' => $this->userFullName,
                    'type'  => $this->textFiltertype
                ],
                'status'           => [
                    'value' => [$this->status]
                ]
            ]
        ];

        return $this->arrayToLink($params);
    }
}
