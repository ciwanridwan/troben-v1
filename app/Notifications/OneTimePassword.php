<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Qontak\WhatsApp\Message;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\OneTimePassword as OneTimePasswordModel;
use NotificationChannels\Qontak\WhatsApp\BusinessChannel;
use NotificationChannels\Qontak\Contracts\QontakNotification;

class OneTimePassword extends Notification implements QontakNotification
{
    const WHATSAPP_TEMPLATE_ID = '61be42f5-fd45-44f9-a510-e919e22d23bc';
    const WHATSAPP_CHANNEL_ID = 'dec6c223-0d5a-4fc8-8381-9be441828b0c';

    /**
     * OTP model instance.
     *
     * @var \App\Models\OneTimePassword
     */
    protected OneTimePasswordModel $otp;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\OneTimePassword $oneTimePassword
     */
    public function __construct(OneTimePasswordModel $oneTimePassword)
    {
        $this->otp = $oneTimePassword;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', BusinessChannel::class]; // NusaSMS::class
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /** {@inheritdoc} */
    public function toQontak($notifiable): Message
    {
        $message = new Message(self::WHATSAPP_TEMPLATE_ID, self::WHATSAPP_CHANNEL_ID);
        $message->addBody('1', 'full_name', $this->otp->token);

        return $message;
    }
}
