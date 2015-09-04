<?php
namespace Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy;

use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\DeskBundle\Model\Ticket\WatcherList;
use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;
use Diamante\UserBundle\Api\UserService;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\UserBundle\Model\User;

/**
 * Class EmailNotification
 * @package Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy
 */
class EmailNotification
{
    /**
     * @var array
     */
    private $emailConstantsPreSet = [
        'watchers',
        'reporter',
        'assignee',
    ];

    const CONFIG_SENDER_NAME_PATH = 'oro_notification.email_notification_sender_name';
    const CONFIG_SENDER_EMAIL_PATH = 'oro_notification.email_notification_sender_email';

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ProcessorInterface
     */
    protected $listedEntityProcessor;

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

            if (in_array($email, $this->emailConstantsPreSet, true)) {
                $recipients = array_merge($recipients, $this->resolveEmailPreSetRecipients($email));
                continue;
            }

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
     * @param string $email
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
     * TODO: Cases should be moved to separate classes
     *
     * @param string $preset
     * @return array
     */
    protected function resolveEmailPreSetRecipients($preset)
    {
        $recipients = [];

        $ticket = $this->getListedEntityProcessor()->getTicketEntity($this->getContext()->getTarget());

        switch ($preset) {
            case 'watchers':
                /** @var WatcherList $watcher */
                foreach ($ticket->getWatcherList() as $watcher)
                {
                    $details = $this->userService->fetchUserDetails(User::fromString($watcher->getUserType()));
                    $recipients[$details->getFullName()] = $details->getEmail();
                }

                break;
            case 'reporter':
                $details = $this->userService->fetchUserDetails($ticket->getReporter());
                $recipients[$details->getFullName()] = $details->getEmail();

                break;
            case 'assignee':
                $assignee = $ticket->getAssignee();

                if (!$assignee) {
                    break;
                }

                $assigneeUser = new User($assignee->getId(), User::TYPE_ORO);
                $details = $this->userService->fetchUserDetails($assigneeUser);
                $recipients[$details->getFullName()] = $details->getEmail();

                break;
        }

        return $recipients;
    }

    /**
     * @return ProcessorInterface
     */
    public function getListedEntityProcessor()
    {
        return $this->listedEntityProcessor;
    }

    /**
     * @param ProcessorInterface $listedEntityProcessor
     */
    public function setListedEntityProcessor($listedEntityProcessor)
    {
        $this->listedEntityProcessor = $listedEntityProcessor;
    }

}