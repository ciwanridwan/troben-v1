<?php

namespace App\Notifications\Telegram\TelegramMessages\Finance;

use App\Supports\Emoji;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TransporterBalance extends Notification
{
    protected $chat_id;

    public function __construct()
    {
        $this->chat_id = config('telegram.chat.finance_group');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return ['telegram'];
    }

    /**
     * Get the telegram representation of the notification.
     * @param $notifiable
     * @return \NotificationChannels\Telegram\TelegramFile|\NotificationChannels\Telegram\TelegramLocation|TelegramMessage|\NotificationChannels\Telegram\Traits\HasSharedLogic
     */
    public function toTelegram($notifiable)
    {
        $telegramResponseText =  Emoji::generateEmoji(Emoji::EMOJI_RED_FLAG). "* KOMISI MITRA!*\n".
            "*Harga mitra transporter tidak ditemukan*\n".
            "Mitra code: *$notifiable[partner_code]*\n".
            "Kode manifest: *$notifiable[manifest_code]*\n".
            "Jumlah resi: *$notifiable[package_count]*\n".
            "Berat manifest terhitung: *$notifiable[manifest_weight] Kg*\n";

        return TelegramMessage::create()
            ->to($this->chat_id)
            ->content($telegramResponseText);
    }

}
