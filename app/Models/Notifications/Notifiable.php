<?php

namespace App\Models\Notifications;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Notifiable Model.
 *
 * @property string $notification_id
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property \Carbon\Carbon $read_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Notifiable extends MorphPivot
{
    use HasFactory, CustomSerializeDate;

    protected $table = 'notifiable';
}
