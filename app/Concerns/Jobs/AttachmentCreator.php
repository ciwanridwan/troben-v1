<?php

namespace App\Concerns\Jobs;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait AttachmentCreator
{
    /**
     * Create any attachment
     *
     * @param UploadedFile $file
     * @param array $attributes
     * @return Attachment
     */
    public function create(UploadedFile $file, array $attributes): Attachment
    {
        $attributes['path'] = $this->storeFile($file, $attributes['disk'] ?? config('attachment.disk'));
        $attributes['mime'] = $this->getUploadedFileMime($file);

        return $this->save($attributes);
    }

    /**
     * Store file into disk
     *
     * @param UploadedFile $file
     * @param string $disk
     * @return false|string
     */
    public function storeFile(UploadedFile $file, string $disk)
    {
        $composes = Str::orderedUuid();

        $fileName = $composes.'.'.$file->getClientOriginalExtension();
        $path = substr($composes, 0, 2).'/'.substr($composes, 2, 2).'/'.substr($composes, 4, 2);

        return $file->storeAs($path, $fileName, [
            'disk' => $disk,
        ]);
    }

    /**
     * Create attachment from file that have been stored in filesystems.
     *
     * @param string $path
     * @param array $attributes
     * @param null $disk
     * @return Attachment
     */
    public function createFromPath(string $path, array $attributes, $disk = null): Attachment
    {
        $attributes['disk'] = $disk ?? config('attachment.disk');
        $attributes['path'] = $path;
        $attributes['mime'] = $this->getStorageFileMime($attributes['disk'], $attributes['path']);

        return $this->save($attributes);
    }

    /**
     * Get file mimetype that retrieved from laravel uploaded file instance.
     *
     * @param UploadedFile $file
     * @return string|null
     */
    private function getUploadedFileMime(UploadedFile $file): ?string
    {
        return $file->getMimeType();
    }

    /**
     * Get file mimetype that retrieved from specific filesystem.
     *
     * @param string $disk
     * @param string $path
     * @return string
     */
    private function getStorageFileMime(string $disk, string $path): string
    {
        return Storage::disk($disk)->mimeType($path);
    }

    /**
     * Save attachment into database.
     *
     * @param array $attributes
     * @return Attachment
     */
    private function save(array $attributes): Attachment
    {
        $class = config('attachment.model');
        $attachment = new $class();

        $attachment->fill(
            Arr::only($attributes, ['title', 'mime', 'type', 'path', 'disk', 'options', 'description'])
        );

        $attachment->save();

        return $attachment;
    }

}
