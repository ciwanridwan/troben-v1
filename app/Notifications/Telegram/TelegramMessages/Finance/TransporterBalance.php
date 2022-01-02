<?php

namespace App\Notifications\Telegram\TelegramMessages\Finance;

use App\Supports\Emoji;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

class TransporterBalance extends Notification
{
    public const MESSAGE_TYPE_DELIVERY = 1; # attributes type for delivery
    public const MESSAGE_TYPE_PACKAGE = 2; # attributes type for package

    /**
     * represent telegram chat id
     *
     * @var string|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected string $chat_id;

    /**
     * represent attributes that use
     *
     * @var array $attributes
     */
    protected array $attributes;

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
        $this->attributes = $notifiable;
        $telegramResponseText = match ($this->attributes['type']) {
            self::MESSAGE_TYPE_DELIVERY => $this->deliveryResponseText(),
            self::MESSAGE_TYPE_PACKAGE => $this->packageResponseText(),
            default => false,
        };

        if (!$telegramResponseText) {
            Log::warning('telegram response text not available',['attributes' => $this->attributes]);
            $this->chat_id = config('telegram.chat.app_group');
            $telegramResponseText = $this->alertResponseText();
        }

        return TelegramMessage::create()
            ->to($this->chat_id)
            ->content($telegramResponseText);
    }

    private function deliveryResponseText(): string
    {
        return Emoji::generateEmoji(Emoji::EMOJI_RED_FLAG). "* KOMISI MITRA!*\n".
            "*Harga transit mitra transporter tidak ditemukan*\n".
            "Mitra code: *".$this->attributes['partner_code']."*\n".
            "Kode manifest: *".$this->attributes['manifest_code']."*\n".
            "Jumlah resi: *".$this->attributes['package_count']."*\n".
            "Berat manifest terhitung: *".$this->attributes['manifest_weight']." Kg*\n";
    }

    private function packageResponseText(): string
    {
        return Emoji::generateEmoji(Emoji::EMOJI_RED_FLAG). "* KOMISI MITRA!*\n".
            "*Harga dooring mitra transporter tidak ditemukan*\n".
            "Mitra code: *".$this->attributes['partner_code']."*\n".
            "Manifest code: *".$this->attributes['manifest_code']."*\n".
            "Origin regency: *".$this->attributes['origin']."*\n".
            "Destination: *".$this->attributes['destination']."*\n".
            "Kode resi: *".$this->attributes['package_code']."*\n".
            "Berat resi terhitung: *".$this->attributes['package_weight']." Kg*\n";
    }

    private function alertResponseText(): string
    {
        return Emoji::generateEmoji(Emoji::EMOJI_MEGAPHONE). "* ALERT!*\n".
            "*Fail to send notification to finance: *\n".
            "Attributes: \n".
            json_encode($this->attributes);
    }
}
