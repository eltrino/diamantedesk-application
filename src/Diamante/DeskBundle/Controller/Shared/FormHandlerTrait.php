<?php

namespace Diamante\DeskBundle\Controller\Shared;


use Diamante\DeskBundle\Exception\ValidatorException;
use Symfony\Component\Form\FormInterface;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

trait FormHandlerTrait
{
    /**
     * @param FormInterface $form
     * @throws MethodNotAllowedException
     * @throws ValidatorException
     */
    protected function handle(Request $request, Form $form)
    {

        if (false === $request->isMethod('POST')) {
            throw new MethodNotAllowedException(array('POST'), 'Form can be posted only by "POST" method.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && false === $form->isValid()) {
            throw new ValidatorException('Form object validation failed, form is invalid.');
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    abstract public function getRequest() ;
}