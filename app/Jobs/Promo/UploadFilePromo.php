<?php

namespace App\Jobs\Promo;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Promos\Promo;
use Illuminate\Http\UploadedFile;

class UploadFilePromo
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'cover';

    public Promo $promo;

    private UploadedFile $cover;

    public function __construct(Promo $promo, UploadedFile $cover)
    {
        $this->promo = $promo;
        $this->cover = $cover;
    }

    public function handle()
    {
        $this->promo->attachments()->updateOrCreate([
            'type' => Promo::ATTACHMENT_COVER,
        ], [
            'type' => Promo::ATTACHMENT_COVER,
            'title' => $this->cover->getClientOriginalName(),
            'path' => $this->storeFile($this->cover, self::DISK_DRIVER),
            'disk' => self::DISK_DRIVER,
            'mime' => $this->getUploadedFileMime($this->cover),
        ]);
    }
}
