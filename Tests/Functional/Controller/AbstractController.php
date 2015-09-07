<?php
namespace Diamante\DeskBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractController extends WebTestCase
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param array|string $gridParameters
     * @param array $filter
     * @return Response
     */
    public function requestGrid($gridParameters, $filter = array())
    {
        if (is_string($gridParameters)) {
            $gridParameters = array('gridName' => $gridParameters);
        }

        //transform parameters to nested array
        $parameters = array();
        foreach ($filter as $param => $value) {
            $param .= '=' . $value;
            parse_str($param, $output);
            $parameters = array_merge_recursive($parameters, $output);
        }

        $gridParameters = array_merge_recursive($gridParameters, $parameters);

        $this->client->request(
            'GET',
            $this->getUrl('oro_datagrid_index', $gridParameters)
        );

        return $this->client->getResponse();
    }
}