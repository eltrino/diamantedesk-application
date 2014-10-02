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
namespace Diamante\EmailProcessingBundle\Infrastructure\Message\Zend;

abstract class AbstractMessageProvider
{
    /**
     * Retrieves Message ID
     *
     * @param \Zend\Mail\Headers $headers
     * @return string|null
     */
    protected function processMessageId($headers)
    {
        $messageId = null;
        if ($headers->get('messageid')) {
            preg_match('/<([^<]+)>/', $headers->get('messageid')->getFieldValue(), $matches);
            $messageId = $matches[1];
        }
        return $messageId;
    }

    /**
     * @param $headers
     * @return string
     */
    public function processFrom($headers)
    {
        $messageFrom = $headers->get('from')->getAddressList()->key();
        return $messageFrom;
    }

    /**
     * @param $headers
     * @return string
     */
    public function processTo($headers)
    {
        $messageTo = $headers->get('to')->getAddressList()->key();
        return $messageTo;
    }

    /**
     * Retrieves Message Reference
     *
     * @param \Zend\Mail\Headers $headers
     * @return string|null
     */
    protected function processMessageReference($headers)
    {
        $messageReference = null;
        if ($headers->get('references')) {
            preg_match('/<([^<]+)>/', $headers->get('references')->getFieldValue(), $matches);
            $messageReference = $matches[1];
        }
        return $messageReference;
    }
} 