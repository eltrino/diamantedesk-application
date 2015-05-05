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
namespace Diamante\DeskBundle\Tests\Model\Ticket\EmailProcessing\Services;

use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class MessageReferenceServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_COMMENT_CONTENT = 'dummy_comment_content';
    const DUMMY_CLEANED_COMMENT_CONTENT = "<p>dummy<em>comment</em>content</p>\n";

    /**
     * @var  \Diamante\EmailProcessingBundle\Model\Service\EmailFilterService
     * @Mock \Diamante\EmailProcessingBundle\Model\Service\EmailFilterService
     */
    private $emailFilterService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->emailFilterService->__construct();
    }

    /**
     * @test
     */
    public function thatCommentContainsOnlyLastResponse()
    {
        $rawComment = self::DUMMY_COMMENT_CONTENT
            . MessageReferenceServiceImpl::DELIMITER_LINE
            . self::DUMMY_COMMENT_CONTENT
            . MessageReferenceServiceImpl::DELIMITER_LINE
            . self::DUMMY_COMMENT_CONTENT;

        $this->emailFilterService
            ->expects($this->once())->method('recognizeUsefulContent')
            ->with(
                $this->equalTo($rawComment)
            )
            ->will($this->returnValue(self::DUMMY_CLEANED_COMMENT_CONTENT));

        $this->emailFilterService->recognizeUsefulContent($rawComment);
    }
}
