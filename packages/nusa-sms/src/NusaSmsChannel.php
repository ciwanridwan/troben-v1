<?php

namespace NotificationChannels\NusaSms;

use Illuminate\Notifications\Notification;

class NusaSmsChannel
{
    /**
     * Nusa Sms Client Instance.
     *
     * @var \NotificationChannels\NusaSms\NusaSmsClient
     */
    protected NusaSmsClient $nusaSmsClient;

    /**
     * NusaSmsChannel constructor.
     *
     * @param \NotificationChannels\NusaSms\NusaSmsClient $nusaSmsClient
     */
    public function __construct(NusaSmsClient $nusaSmsClient)
    {
        $this->nusaSmsClient = $nusaSmsClient;
    }

    /**
     * Send SMS via NUSA SMS.
     *
     * @param                                        $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toNusaSms($notifiable);

        $to = $notifiable->routeNotificationFor('sms');

        return $this->nusaSmsClient->send($message, $to);
    }
}
