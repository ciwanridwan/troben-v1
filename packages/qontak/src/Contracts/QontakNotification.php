<?php

namespace NotificationChannels\Qontak\Contracts;

use NotificationChannels\Qontak\WhatsApp\Message;

interface QontakNotification
{
    /**
     * Get Qontak message.
     *
     * @param $notifiable
     *
     * @return \NotificationChannels\Qontak\WhatsApp\Message
     */
    public function toQontak($notifiable): Message;
}
