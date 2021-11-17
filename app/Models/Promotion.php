<?php

namespace App\Models;

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
 * @property string $type
 * @property Text $terms_and_conditions
 * @property float $min_payment
 * @property float $max_payment
 * @property float $min_weight
 * @property float $max_weight
 *
 * @property bool $is_active
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */

class Promotion extends Model implements AttachableContract
{
    use SoftDeletes, CustomSerializeDate, HashableId, HasFactory, Attachable, CanSearch;

    public const ATTACHMENT_COVER = 'promotion';

    protected $table = 'promotion';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'type',
        'terms_and_conditions',
        'min_payment',
        'max_payment',
        'min_weight',
        'max_weight',
        'is_active',
        'start_date',
        'end_date',
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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
