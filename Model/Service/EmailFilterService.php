<?php
namespace Diamante\EmailProcessingBundle\Model\Service;

use Diamante\EmailProcessingBundle\Model\Service\EmailFilter\Thunderbird\MozCiteFilter;
use EmailCleaner\EmailCleaner;
use HTMLPurifier;
use HTMLPurifier_Config;
use Michelf\Markdown;

class EmailFilterService
{
    const DELIMITER_LINE = '[[ Please reply above this line ]]';

    /**
     * @var string;
     */
    protected $content;

    /**
     * @var Markdown
     */
    protected $markdown;

    /**
     * @var \Tidy
     */
    protected $HTMLPurifier;

    /**
     * @var EmailCleaner
     */
    protected $emailCleaner;

    /**
     * @var array
     */
    protected $notUsedFilters = [
        'EmailCleaner\DefaultFilters\Shared\ScriptFilter',
        'EmailCleaner\DefaultFilters\Shared\BodyLeaveFilter',
        'EmailCleaner\DefaultFilters\Shared\TableFilter',
        'EmailCleaner\DefaultFilters\Shared\TagsFilter',
        'EmailCleaner\DefaultFilters\Shared\AttributesRemoverFilter',
    ];

    /**
     * @param $content string
     */
    public function __construct($content = null)
    {
        if ($content) {
            $this->setContent($content);
        }

        $this->HTMLPurifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
        $this->markdown = new Markdown();

        $this->emailCleaner = new EmailCleaner();

        foreach ($this->notUsedFilters as $filterClass) {
            foreach ($this->emailCleaner->filters as $key => $foundFilter) {
                if ($foundFilter instanceof $filterClass) {
                    unset($this->emailCleaner->filters[$key]);
                    break;
                }
            }
        }

        $this->emailCleaner->addFilter(new MozCiteFilter());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content string
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Main function for prepare the useful email content
     *
     * @param $content string
     * @return string
     */
    public function recognizeUsefulContent($content = null)
    {
        if (!$content) {
            $content = $this->getContent();
        }

        if ($this->isHtml($content)) {
            $content = $this->HTMLPurifier->purify($content);
        } else {
            $content = $this->markdown->defaultTransform($content);
        }

        return $content;
    }

    /**
     * @param $content string
     * @return string
     */
    public function cleanUpTicketContent($content = null)
    {
        if (!$content) {
            $content = $this->getContent();
        }

        return $content;
    }

    /**
     * @param $content string
     * @return string
     */
    public function cleanUpCommentContent($content = null)
    {
        if (!$content) {
            $content = $this->getContent();
        }

        if ($this->isHtml($content)) {
            $content = $this->removeReplies($content);
        } else {
            $position = strpos($content, self::DELIMITER_LINE);
            if ($position !== false) {
                $content = substr($content, 0, $position);
            }
        }

        return $content;
    }

    /**
     * @param $content string
     * @return bool
     */
    protected function isHtml($content)
    {
        return $content !== strip_tags($content);
    }

    /**
     * @param $content string
     * @return mixed
     */
    protected function removeReplies($content)
    {
        $content = $this->emailCleaner->setHTML($content)->parse();

        return $content;
    }

}