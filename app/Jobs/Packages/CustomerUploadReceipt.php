<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use Illuminate\Http\UploadedFile;
use Jalameta\Attachments\Concerns\AttachmentCreator;

class CustomerUploadReceipt
{
    use AttachmentCreator;

    public Package $package;

    private UploadedFile $receipt;

    public function __construct(Package $package, UploadedFile $receipt)
    {
        $this->package = $package;
        $this->receipt = $receipt;
    }

    public function handle()
    {
        $attachment = $this->create($this->receipt, [
            'title' => Package::ATTACHMENT_RECEIPT,
        ]);

        $this->package->attachments()->attach($attachment);
    }
}
