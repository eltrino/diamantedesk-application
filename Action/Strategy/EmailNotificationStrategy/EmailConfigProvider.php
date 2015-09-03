<?php
namespace Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Class EmailConfigProvider
 * @package Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy
 */
class EmailConfigProvider
{
    const CONFIG_SENDER_NAME_PATH = 'oro_notification.email_notification_sender_name';
    const CONFIG_SENDER_EMAIL_PATH = 'oro_notification.email_notification_sender_email';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var string
     */
    protected $senderName;

    /**
     * @var string
     */
    protected $senderEmail;

    /**
     * @var string
     */
    private $transport;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $encryption;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @param ConfigManager $configManager
     * @param string $transport
     * @param string $host
     * @param string $port
     * @param string $encryption
     * @param string $user
     * @param string $password
     */
    public function __construct(
        ConfigManager $configManager,
        $transport,
        $host,
        $port,
        $encryption,
        $user,
        $password
    ) {
        $this->configManager = $configManager;
        $this->transport = $transport;
        $this->host = $host;
        $this->port = $port;
        $this->encryption = $encryption;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return array|string
     */
    public function getSenderName()
    {
        if (!$this->senderName) {
            $this->senderName = $this->configManager->get(self::CONFIG_SENDER_NAME_PATH, '');
        }
        return $this->senderName;
    }

    /**
     * @return array|string
     */
    public function getSenderEmail()
    {
        if (!$this->senderEmail) {
            $this->senderEmail = $this->configManager->get(self::CONFIG_SENDER_EMAIL_PATH, '');
        }
        return $this->senderEmail;
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}