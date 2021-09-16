<?php

namespace App\Jobs\Packages\Item;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class WarehouseUploadItem implements ShouldQueue
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'package';

    public Item $item;

    public Package $package;

    private UploadedFile $photo;

    private Collection $photos;

    public function __construct(Package $package, Item $item, array $photos)
    {
        $this->item = $item;
        $this->package = $package;
        $this->photos = collect($photos);

    }

    public function handle()
    {
        $this->photos->each(function ($photo) {
            $this->photo = $photo;
            $this->package->attachments()->create([
                'type' => Package::ATTACHMENT_PACKAGE,
                'title' => $this->photo->getClientOriginalName(),
                'path' => $this->storeFile($this->photo, self::DISK_DRIVER),
                'disk' => self::DISK_DRIVER,
                'mime' => $this->getUploadedFileMime($this->photo),
            ]);
        });
    }
}
