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
namespace Diamante\FrontBundle\Tests\Infrastructure;

use Diamante\FrontBundle\Infrastructure\RegistrationMailer;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class RegistrationMailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Twig_Environment
     * @Mock \Twig_Environment
     */
    private $twig;

    /**
     * @var \Swift_Mailer
     * @Mock \Swift_Mailer
     */
    private $mailer;

    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    public function testSendConfirmationEmail()
    {
        $email = 'test@email.com';
        $activationHash = md5(time());
        $fromEmail = 'from@email.com';
        $htmlTemplate = 'html.tpl';
        $txtTemplate  = 'txt.tpl';

        $registrationMailer = new RegistrationMailer(
            $this->twig, $this->mailer, $fromEmail, $htmlTemplate, $txtTemplate
        );

        $confirmation = new \Swift_Message();

        $renderedHtmlTpl = 'rendered html';
        $renderedTxtTpl  = 'rendered txt';

        $this->twig->expects($this->at(0))->method('render')
            ->with($txtTemplate)->will($this->returnValue($renderedTxtTpl));
        $this->twig->expects($this->at(1))->method('render')
            ->with($htmlTemplate)->will($this->returnValue($renderedHtmlTpl));

        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($confirmation));
        $this->mailer->expects($this->once())->method('send')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Swift_Message'),
                $this->callback(function(\Swift_Message $other) use ($email, $renderedTxtTpl, $fromEmail) {
                    return $other->getSubject() == 'Confirmation'
                    && $other->getBody() == $renderedTxtTpl
                    && array_key_exists($email, $other->getTo())
                    && array_key_exists($fromEmail, $other->getReplyTo());
                })
            )
        );

        $registrationMailer->sendConfirmationEmail($email, $activationHash);
    }
}
