<?php

namespace Diamante\DeskBundle\Controller\Shared;


use Diamante\DeskBundle\Exception\ValidatorException;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

trait FormHandlerTrait
{
    /**
     * @param Form $form
     * @throws MethodNotAllowedException
     * @throws ValidatorException
     */
    protected function handle(Form $form)
    {
        if (false === $this->getRequest()->isMethod('POST')) {
            throw new MethodNotAllowedException(array('POST'),'Form can be posted only by "POST" method.');
        }

        $form->handleRequest($this->getRequest());

        if (false === $form->isValid()) {
            throw new ValidatorException('Form object validation failed, form is invalid.');
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    abstract public function getRequest();
}