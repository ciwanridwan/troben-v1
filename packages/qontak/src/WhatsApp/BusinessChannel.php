<?php

namespace NotificationChannels\Qontak\WhatsApp;

use Illuminate\Notifications\Notification;
use NotificationChannels\Qontak\QontakClient;
use NotificationChannels\Qontak\Contracts\QontakNotification;
use NotificationChannels\Qontak\Exceptions\CouldNotSendNotification;

class BusinessChannel
{
    /**
     * Qontak Client.
     *
     * @var \NotificationChannels\Qontak\QontakClient
     */
    protected QontakClient $qontak;

    /**
     * BusinessChannel constructor.
     *
     * @param \NotificationChannels\Qontak\QontakClient $qontak
     */
    public function __construct(QontakClient $qontak)
    {
        $this->qontak = $qontak;
    }

    /**
     * Send the given notification.
     *
     * @param mixed                                  $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \Throwable
     */
    public function send($notifiable, Notification $notification)
    {
        throw_if(! $notification instanceof QontakNotification, CouldNotSendNotification::invalidNotificationType());

        /** @var \NotificationChannels\Qontak\Contracts\QontakNotification $notification */
        $template = $notification->toQontak($notifiable);

        $this->qontak->getHttpClient()->request('POST', '/api/open/v1/broadcasts/whatsapp/direct', [
            'headers' => [
                'Authorization' => 'Bearer ',
                'Content-Type' => 'application/json',
            ],
            'json' => $template->toWhatsAppParams($notifiable),
        ]);
    }
}
