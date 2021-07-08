<?php

namespace App\Jobs\Packages\Item;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Packages\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class WarehouseUploadItem
{
    use AttachmentCreator;

    public const DISK_DRIVER = 'package_item';

    public Item $item;

    private UploadedFile $photo;

    private Collection $photos;

    public function __construct(Item $item, array $photos)
    {
        $this->item = $item;

        $this->photos = collect($photos);
    }

    public function handle()
    {
        $this->photos->each(function ($photo) {
            $this->photo = $photo;
            $this->item->attachments()->create([
                'type' => Item::ATTACHMENT_PACKAGE_ITEM,
                'title' => $this->photo->getClientOriginalName(),
                'path' => $this->storeFile($this->photo, self::DISK_DRIVER),
                'disk' => self::DISK_DRIVER,
                'mime' => $this->getUploadedFileMime($this->photo),
            ]);
        });
    }
}
