<?php

namespace App\Models;

use Jalameta\Attachments\Entities\Attachment as BaseAttachment;

class Attachment extends BaseAttachment
{
    public function getUriAttribute()
    {
        return route('home.attachment', [
            'attachment_uuid' => $this->getKey(),
        ]);
    }
}
