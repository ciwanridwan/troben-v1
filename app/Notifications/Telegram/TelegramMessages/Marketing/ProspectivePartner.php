<?php

namespace App\Notifications\Telegram\TelegramMessages\Marketing;

use App\Models\Partners\Partner;
use App\Supports\Emoji;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use NotificationChannels\Telegram\TelegramMessage;

class ProspectivePartner extends Notification
{
    private string $chat_id;

    /**
     * Prospective Partner notification construct.
     * Setup config telegram bot token and set chat id
     *
     * @return void
     */
    public function __construct()
    {
        Config::set('services.telegram-bot-api.token',config('telegram.bot.ray_bot_token'));
        $this->chat_id = config('telegram.chat.new_partner_group');
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
     * @param $notifiable
     * @return \NotificationChannels\Telegram\TelegramFile|\NotificationChannels\Telegram\TelegramLocation|TelegramMessage|\NotificationChannels\Telegram\Traits\HasSharedLogic
     */
    public function toTelegram($notifiable)
    {
        $message = Emoji::generateEmoji(Emoji::EMOJI_RED_FLAG)."* Prospek Trawlbens!*\n".
        "*Terdapat prospek yang melakukan pendaftaran dengan jenis kemitraan ";
        if ($notifiable['type'] === Partner::TYPE_BUSINESS) $message .= "'Mitra Bisnis'";
        elseif ($notifiable['type'] === Partner::TYPE_POOL) $message .= "'Mitra Pool Warehouse'";
        elseif ($notifiable['type'] === Partner::TYPE_SPACE) $message .= "'Mitra Space'";
        elseif ($notifiable['type'] === Partner::TYPE_TRANSPORTER) $message .= "'Mitra Transporter Mobil'";
        else $message .= "'Mitra Kurir Motor'";
        $message .= "*\n";

        $message .= "Nama: *".$notifiable['nama_depan']." ".$notifiable['nama_belakang']."*\n".
            'No. HP: *'.$notifiable['handphone'];
        if (! empty($notifiable['handphone1'])) $message .= " / ".$notifiable['handphone1'];

        $message .= "*\n".
            'Email: *'.$notifiable['email']."*\n".
            'Alamat: *'.$notifiable['alamat']."*\n".
            'Kota: *'.$notifiable['kota']."*\n";

        if (in_array($notifiable['type'],[Partner::TYPE_BUSINESS,Partner::TYPE_SPACE,Partner::TYPE_POOL])) $message .= 'Alamat Usaha: *'.$notifiable['alamat_usaha']."*\n".
            'Luas Lahan / Luas Bangunan: *'.$notifiable['luas_tanah']." / ".$notifiable['luas_bangunan']."*\n".
            'Status Bangunan: *'.$notifiable['status_lahan']."*\n";

        if ($notifiable['type'] === Partner::TYPE_POOL) {
            $message .= "*Data Alat*\n".
                'Jenis Alat: *'.$notifiable['jenis_alat1']." (sebanyak $notifiable[jumlah_alat1] dengan tahun $notifiable[tahun_alat_1])* ";
            if (! empty($notifiable['jenis_alat2'])) {
                $message .= "*dan ada alat lainnya.. *";
            }
            $message .= "\n";
        }

        if (in_array($notifiable['type'],[Partner::TYPE_BUSINESS,Partner::TYPE_TRANSPORTER])) {
            $message .= 'Armada: *'.$notifiable['jenis_kendaraan1']." (sebanyak $notifiable[jumlah_kendaraan1] dengan tahun $notifiable[tahun_kendaraan_1])* ";

            if (! empty($notifiable['jenis_kendaraan2'])) {
                $message .= "*dan ada kendaraan lainnya.. *";
            }
            $message .= "\n";
        }

        if (! in_array($notifiable['type'],Partner::getAvailableTypes())) $message .= 'Merek Kendaraan: *'.$notifiable['merek']."*\n".
            'Tipe Kendaraan: *'.$notifiable['tipe']."*\n".
            'Tahun Kendaraan: *'.$notifiable['tahun']."*\n";

        if ($notifiable['type'] === Partner::TYPE_BUSINESS) $url = 'https://crm.trawlbens.id/administrator/mitra_business';
        elseif ($notifiable['type'] === Partner::TYPE_POOL) $url = 'https://crm.trawlbens.id/administrator/mitra_pool_warehouse';
        elseif ($notifiable['type'] === Partner::TYPE_SPACE) $url = 'https://crm.trawlbens.id/administrator/mitra_space';
        elseif ($notifiable['type'] === Partner::TYPE_TRANSPORTER) $url = 'https://crm.trawlbens.id/administrator/mitra_transporter_mobil';
        else $url = 'https://crm.trawlbens.id/administrator/mitra_kurir_motor';

        return TelegramMessage::create()
            ->to($this->chat_id)
            ->content($message)
            ->button('More info', $url);
    }
}
