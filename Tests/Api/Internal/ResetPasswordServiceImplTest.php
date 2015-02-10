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

namespace Diamante\FrontBundle\Tests\Api\Internal;

use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

use Diamante\FrontBundle\Api\Internal\ResetPasswordServiceImpl;

class ResetPasswordServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ResetPasswordServiceImpl
     */
    private $resetPasswordService;

    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     * @Mock \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUserRepository
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUserFactory
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUserFactory
     */
    private $apiUserFactory;

    /**
     * @var \Diamante\FrontBundle\Model\ResetPasswordMailer
     * @Mock \Diamante\FrontBundle\Model\ResetPasswordMailer
     */
    private $resetPasswordMailer;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->resetPasswordService = new ResetPasswordServiceImpl($this->diamanteUserRepository,
            $this->apiUserRepository,
            $this->apiUserFactory,
            $this->resetPasswordMailer);
    }

    public function testGenerateHash()
    {
        $emailAddress = 'max@gmail.com';

        $this->resetPasswordService->generateHash($emailAddress);
    }

}
 