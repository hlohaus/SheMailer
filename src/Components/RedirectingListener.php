<?php declare(strict_types=1);

namespace She\Mailer\Components;

use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swift_Events_SendEvent;
use Swift_Plugins_RedirectingPlugin;
use Symfony\Component\HttpFoundation\RequestStack;

class RedirectingListener extends Swift_Plugins_RedirectingPlugin
{
    /**
     * @var bool
     */
    private $hasRecipient;

    public function __construct(SystemConfigService $config, RequestStack $requestStack)
    {
        $request = $requestStack->getMasterRequest();

        $channelId = null;
        if ($request !== null) {
            $channelId = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        }

        $recipient = $config->get('SheMailer.config.deliveryAddress', $channelId);
        if (!empty($recipient)) {
            $this->hasRecipient = true;
            $recipient = explode("\n", $recipient);
        } else {
            $this->hasRecipient = false;
            $recipient = [];
        }

        $whitelist = $config->get('SheMailer.config.deliveryWhitelist', $channelId);
        if (!empty($whitelist)) {
            $whitelist = (array)$whitelist;
        } else {
            $whitelist = [];
        }

        parent::__construct($recipient, $whitelist);
    }

    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        if ($this->hasRecipient) {
            parent::beforeSendPerformed($evt);
        }
    }

    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        if ($this->hasRecipient) {
            parent::sendPerformed($evt);
        }
    }
}