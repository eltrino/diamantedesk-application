<?php
namespace Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy;

use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;
use Diamante\UserBundle\Api\UserService;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;

/**
 * Class EmailNotification
 * @package Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy
 */
class EmailNotification implements EmailNotificationInterface
{

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var EmailTemplate
     */
    protected $template;

    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * Gets a template can be used to prepare a notification message
     * @return EmailTemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param EmailTemplate $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Gets a list of email addresses can be used to send a notification message
     * @return string[]
     */
    public function getRecipientEmails()
    {
        $recipients = [];

        $arguments = $this->getContext()->getActionArguments();
        if (!property_exists($arguments, 'recipients')) {
            return $recipients;
        }

        foreach ($arguments->recipients as $email) {
            $recipients[$email] = $this->getUserName($email);
        }

        return $recipients;
    }

    /**
     * @param ExecutionContext $context
     */
    public function setContext(ExecutionContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return ExecutionContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param $email
     * @return string
     */
    private function getUserName($email)
    {
        $user = $this->userService->getUserByEmail($email);
        if ($user) {
            $userDetails = $this->userService->fetchUserDetails($user);
            return sprintf("%s %s", $userDetails->getFirstName(), $userDetails->getLastName());
        }
        $recipient = new MessageRecipient($email, null);
        return sprintf("%s %s", $recipient->getFirstName(), $recipient->getLastName());
    }

}