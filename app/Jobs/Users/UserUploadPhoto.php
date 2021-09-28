<?php

namespace App\Jobs\Users;

use App\Concerns\Jobs\AttachmentCreator;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class UserUploadPhoto implements ShouldQueue
{
    use AttachmentCreator;

    public const DISK_USER = 'avatar';

    public User $user;

    private UploadedFile $photo;

    private Collection $photos;

    public function __construct(User $user, array $photos)
    {
        $this->user = $user;

        $this->photos = collect($photos);
    }

    public function handle()
    {
        $this->photos->each(function ($photo) {
            $this->photo = $photo;
            $this->user->attachments()->create([
                'type' => User::ATTACHMENT_PHOTO_PROFILE,
                'title' => $this->photo->getClientOriginalName(),
                'path' => $this->storeFile($this->photo, self::DISK_USER),
                'disk' => self::DISK_USER,
                'mime' => $this->getUploadedFileMime($this->photo),
            ]);
        });
    }
}
