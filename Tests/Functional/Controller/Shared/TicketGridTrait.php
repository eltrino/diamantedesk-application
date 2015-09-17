<?php

namespace Diamante\DeskBundle\Tests\Functional\Controller\Shared;

trait TicketGridTrait
{
    protected function chooseTicketFromGrid()
    {
        $result = $this->getTicketGridData();

        return current($result['data']);
    }

    protected function getTicketGridData()
    {
        $response = $this->requestGrid(
            'diamante-ticket-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        return $this->jsonToArray($response->getContent());
    }
}