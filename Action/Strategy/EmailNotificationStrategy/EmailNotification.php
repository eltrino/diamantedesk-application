<?php
namespace Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy;

use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;
use Diamante\UserBundle\Api\UserService;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;

/**
 * Class EmailNotification
 * @package Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy
 */
class EmailNotification
{
    const CONFIG_SENDER_NAME_PATH = 'oro_notification.email_notification_sender_name';
    const CONFIG_SENDER_EMAIL_PATH = 'oro_notification.email_notification_sender_email';

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
            $recipients[$this->getUserName($email)] = $email;
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
    public function getUserName($email)
    {
        $user = $this->userService->getUserByEmail($email);
        if ($user) {
            $userDetails = $this->userService->fetchUserDetails($user);
            return sprintf("%s %s", $userDetails->getFirstName(), $userDetails->getLastName());
        }
        $recipient = new MessageRecipient($email, null);
        return sprintf("%s %s", $recipient->getFirstName(), $recipient->getLastName());
    }

    /**
     * @return UserService
     */
    public function getUserService()
    {
        return $this->userService;
    }

    /**
     * @param UserService $userService
     */
    public function setUserService($userService)
    {
        $this->userService = $userService;
    }

}