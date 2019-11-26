<?php declare(strict_types=1);

namespace She\Mailer\Components;

use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swift_Events_SendEvent as SendEvent;
use Swift_Events_SendListener as SendListenerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ConfigListener implements SendListenerInterface
{
    /**
     * @var SystemConfigService
     */
    private $config;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(SystemConfigService $config, RequestStack $requestStack)
    {
        $this->config = $config;
        $this->requestStack = $requestStack;
    }

    public function beforeSendPerformed(SendEvent $event)
    {
        $mail = $event->getMessage();

        if (empty($mail->getReplyTo())) {
            if ($this->getConfig('replyToAddress')) {
                $mail->setReplyTo($this->getConfig('replyToAddress'));
            }
        }

        if (empty($mail->getReturnPath())) {
            if ($this->getConfig('returnPath')) {
                $mail->setReturnPath($this->getConfig('returnPath'));
            }
        }

        if ($this->getConfig('bccAddress')) {
            $addresses = (array)$mail->getBcc();
            $newAddresses = array_map('trim', explode("\n", $this->getConfig('bccAddress')));
            $addresses = array_merge(array_combine($newAddresses, array_fill(0, count($newAddresses), null)), $addresses);
            $mail->setBcc($addresses);
        }
    }

    public function sendPerformed(SendEvent $event)
    {
    }

    private function getConfig($name)
    {
        return $this->config->get('SheMailer.config.' . $name, $this->getChannelId());
    }

    private function getChannelId()
    {
        $request = $this->requestStack->getMasterRequest();

        if ($request === null) {
            return null;
        }

        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
    }
}