<?php

namespace Diamante\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @Route("/", name="diamante_user_list")
     * @Template()
     */
    public function listAction()
    {
        return [];
    }

    /**
     * @param $id
     * @Route("/view/{id}", name="diamante_user_view", requirements={"id" = "\d+"})
     * @Template()
     *
     * @return array
     */
    public function viewAction($id)
    {
        $user = $this->container->get('doctrine')
            ->getManager()
            ->getRepository('DiamanteUserBundle:DiamanteUser')
            ->get($id);

        return ['entity' => $user];
    }

    /**
     * @Route("/create", name="diamante_user_create")
     * @Template()
     */
    public function createAction()
    {

    }

    /**
     * @param $id
     *
     * @Route("/update/{id}", name="diamante_user_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction($id)
    {

    }

    /**
     * @param $id
     *
     * @Route("/delete/{id}", name="diamante_user_delete", requirements={"id" = "\d+"})
     */
    public function deleteAction($id)
    {

    }

    protected function edit()
    {

    }
}
