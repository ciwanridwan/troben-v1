<?php

namespace App\Jobs\Customers;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Attachment;
use App\Models\Customers\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerUploadPhoto implements ShouldQueue
{
    use AttachmentCreator;

    public const DISK_CUSTOMER = 'avatar';

    public Customer $customer;

    private UploadedFile $photo;

    private Collection $photos;

    public function __construct(Customer $customer, array $photos)
    {
        $this->customer = $customer;

        $this->photos = collect($photos);
    }

    public function handle()
    {
        $attachable = DB::table('attachable')
            ->where('attachable_id', $this->customer->id)
            ->first();
        if ($attachable == null) {
            $this->photos->each(function ($photo) {
                $this->photo = $photo;
                $this->customer->attachments()->create([
                    'type' => Customer::ATTACHMENT_PHOTO_PROFILE,
                    'title' => $this->photo->getClientOriginalName(),
                    'path' => $this->storeFile($this->photo, self::DISK_CUSTOMER),
                    'disk' => self::DISK_CUSTOMER,
                    'mime' => $this->getUploadedFileMime($this->photo),
                ]);
            });
        } else {
            $attachable = DB::table('attachable')
                ->where('attachable_id', $this->customer->id)
                ->first();

            $attachment = Attachment::where('id', $attachable->attachment_id)->first();

            Storage::disk(self::DISK_CUSTOMER)->delete($attachment->path);

            $attachment->forceDelete();

            $this->photos->each(function ($photo) {
                $this->photo = $photo;
                $this->customer->attachments()->create([
                    'type' => Customer::ATTACHMENT_PHOTO_PROFILE,
                    'title' => $this->photo->getClientOriginalName(),
                    'path' => $this->storeFile($this->photo, self::DISK_CUSTOMER),
                    'disk' => self::DISK_CUSTOMER,
                    'mime' => $this->getUploadedFileMime($this->photo),
                ]);
            });
        }
    }
}
