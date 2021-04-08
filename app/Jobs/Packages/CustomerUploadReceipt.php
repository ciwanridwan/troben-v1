<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use Illuminate\Http\UploadedFile;
use App\Concerns\Jobs\AttachmentCreator;

class CustomerUploadReceipt
{
    use AttachmentCreator;

    const DISK_DRIVER = 'receipt';

    public Package $package;

    private UploadedFile $receipt;

    public function __construct(Package $package, UploadedFile $receipt)
    {
        $this->package = $package;
        $this->receipt = $receipt;
    }

    public function handle()
    {
        $this->package->attachments()->updateOrCreate([
            'type' => Package::ATTACHMENT_RECEIPT,
        ], [
            'type' => Package::ATTACHMENT_RECEIPT,
            'title' => $this->receipt->getClientOriginalName(),
            'path' => $this->storeFile($this->receipt, self::DISK_DRIVER),
            'disk' => self::DISK_DRIVER,
            'mime' => $this->getUploadedFileMime($this->receipt),
        ]);
    }
}
