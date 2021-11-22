<?php

namespace App\Models\Promos;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use Carbon\Carbon;
use Faker\Provider\Text;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jalameta\Attachments\Concerns\Attachable;
use Jalameta\Attachments\Contracts\AttachableContract;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Class Promo.
 *
 * @property string $title
 * @property Text $content
 * @property string $description
 * @property string $type
 * @property bool $is_active
 * @property string author
 * @property string portal
 * @property string source
 * @property string image
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Promo extends Model implements AttachableContract
{
    use SoftDeletes, CustomSerializeDate, HashableId, HasFactory, Attachable, CanSearch;

    public const ATTACHMENT_COVER = 'cover';

    protected $table = 'promo';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'description',
        'type',
        'is_active',
        'author',
        'portal',
        'source',
        'image',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
