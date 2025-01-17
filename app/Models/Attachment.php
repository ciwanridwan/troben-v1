<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Jalameta\Attachments\Entities\Attachment as BaseAttachment;

class Attachment extends BaseAttachment
{
    protected $appends = [
        'uri',
        'uri_stream',
    ];

    protected $hidden = [
        'pivot',
        'disk',
        'path',
        'mime',
    ];

    protected $fillable = [
        'title',
        'mime',
        'path',
        'disk',
        'type',
        'description',
        'options',
    ];

    public function getUriAttribute()
    {
        return route('home.attachment', [
            'attachment_uuid' => $this->getKey(),
        ]);
    }

    public function getUriStreamAttribute()
    {
        $uri = route('home.attachment', [
            'attachment_uuid' => $this->getKey(),
        ]);

        return sprintf('%s?stream=true', $uri);
    }

    /**
     * Override the disk attribute.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getDiskAttribute()
    {
        return $this->getRawOriginal('disk') ?? config('attachment.disk');
    }

    protected static function boot()
    {
        parent::boot();

        self::updating(function (self $attachment) {
            if ($attachment->isDirty('path')) {
                Storage::disk($attachment->getAttribute('disk'))->delete($attachment->getRawOriginal('path'));
            }
        });
    }
}
