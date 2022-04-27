<?php

namespace App\Jobs\Voucher;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Partners\Voucher;
use Illuminate\Http\UploadedFile;

class UploadFileVoucher
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'voucher';

    public Voucher $voucher;

    private UploadedFile $attachment;

    public function __construct(Voucher $voucher, UploadedFile $cover)
    {
        $this->voucher = $voucher;
        $this->attachment = $cover;
    }

    public function handle()
    {
        $this->voucher->attachments()->updateOrCreate([
            'type' => Voucher::ATTACHMENT_COVER,
        ], [
            'type' => Voucher::ATTACHMENT_COVER,
            'title' => $this->attachment->getClientOriginalName(),
            'path' => $this->storeFile($this->attachment, self::DISK_DRIVER),
            'disk' => self::DISK_DRIVER,
            'mime' => $this->getUploadedFileMime($this->attachment),
        ]);
    }
}
