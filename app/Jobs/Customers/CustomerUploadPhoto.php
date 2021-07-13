<?php

namespace App\Jobs\Customers;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\Customers\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

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
