<?php

namespace NotificationChannels\Qontak\Exceptions;

class CouldNotSendNotification extends \Exception
{
    public static function serviceRespondedWithAnError($response)
    {
        return new static('Descriptive error message.');
    }

    public static function invalidNotificationType()
    {
        return new static('Notification is not valid for Qontak, must implement `QontakNotification` interface.');
    }
}
