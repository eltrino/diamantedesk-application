<?php
namespace Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy;

/**
 * Class EmailNotification
 * @package Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy
 */
class EmailTemplate
{
    const TEMPLATE_TYPE_HTML = 'html';
    const TEMPLATE_TYPE_TXT = 'txt';

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @param string $type
     * @param string $file
     */
    public function addTemplateFile($type, $file)
    {
        $this->files[$type] = $file;
    }

    /**
     * @param $type
     * @return string
     */
    public function getTemplateFile($type)
    {
        if (isset($this->files[$type])) {
            return $this->files[$type];
        }
        return '';
    }
}