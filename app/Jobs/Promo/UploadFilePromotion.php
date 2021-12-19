<?php

namespace App\Jobs\Promo;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Promos\Promotion;
use Illuminate\Http\UploadedFile;

class UploadFilePromotion
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'promotion';

    public Promotion $promotion;

    private UploadedFile $attachment;

    public function __construct(Promotion $promotion, UploadedFile $cover)
    {
        $this->promotion = $promotion;
        $this->attachment = $cover;
    }

    public function handle()
    {
        $this->promotion->attachments()->updateOrCreate([
            'type' => Promotion::ATTACHMENT_COVER,
        ], [
            'type' => Promotion::ATTACHMENT_COVER,
            'title' => $this->attachment->getClientOriginalName(),
            'path' => $this->storeFile($this->attachment, self::DISK_DRIVER),
            'disk' => self::DISK_DRIVER,
            'mime' => $this->getUploadedFileMime($this->attachment),
        ]);
    }
}
