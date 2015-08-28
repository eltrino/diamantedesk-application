<?php
namespace Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy;

use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;

/**
 * Class EmailNotification
 * @package Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy
 */
class EmailTemplate implements EmailTemplateInterface
{
    const TEMPLATE_TYPE_HTML = 'html';
    const TEMPLATE_TYPE_TXT = 'txt';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $files = [];

    /**
     * Gets email template type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets email subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Gets email template content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param string $type
     * @param string $file
     */
    public function addTemplateFile($type, $file)
    {
        $this->files[$type] = $file;
    }
}