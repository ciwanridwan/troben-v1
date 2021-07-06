<?php

namespace App\Jobs\Packages;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Packages\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DriverUploadReceiver implements ShouldQueue
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'received';

    public Package $package;

    private UploadedFile $photo;

    private Collection $photos;

    public function __construct(Package $package, array $photos)
    {
        $this->package = $package;

        $this->photos = collect($photos);

    }

    public function handle()
    {
        $this->photos->each(function ($photo) {
            $this->photo = $photo;
            $this->package->attachments()->create([
                'type' => Package::ATTACHMENT_RECEIVED,
                'title' => $this->photo->getClientOriginalName(),
                'path' => $this->storeFile($this->photo, self::DISK_DRIVER),
                'disk' => self::DISK_DRIVER,
                'mime' => $this->getUploadedFileMime($this->photo),
            ]);
        });
    }
}
