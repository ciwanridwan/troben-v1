<?php

namespace App\Jobs\Promo;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Promo;
use Illuminate\Http\UploadedFile;

class UploadFilePromo
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'video';

    public Promo $promo;

    private UploadedFile $receipt;

    public function __construct(Promo $promo, UploadedFile $receipt)
    {
        $this->promo = $promo;
        $this->receipt = $receipt;
    }

    public function handle()
    {
        $this->promo->attachments()->updateOrCreate([
            'type' => Promo::ATTACHMENT_VIDEO,
        ], [
            'type' => Promo::ATTACHMENT_VIDEO,
            'title' => $this->receipt->getClientOriginalName(),
            'path' => $this->storeFile($this->receipt, self::DISK_DRIVER),
            'disk' => self::DISK_DRIVER,
            'mime' => $this->getUploadedFileMime($this->receipt),
        ]);
    }
}
