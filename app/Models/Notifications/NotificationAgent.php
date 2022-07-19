<?php

namespace App\Models\Notifications;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;

class NotificationAgent extends Model
{
    use SoftDeletes, CustomSerializeDate, HashableId, HasFactory;

    protected $table = 'agent_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'title',
        'message',
        'status',
        'agent_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
