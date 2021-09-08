<?php

namespace App\Models;

use App\Models\Deliveries\Deliverable;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class HistoryReject extends Model
{
    use HasFactory;

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'history_reject';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'delivery_id',
        'partner_id',
        'packages_id',
        'user_id',
        'content',
        'description',
        'status',
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
        'created_at ' => 'datetime',
        'updated_at ' => 'datetime',
    ];


    public function packages(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

//    public function packages(): Relations\MorphToMany
//    {
//        return $this->morphedByMany(Package::class, 'deliverable')
//            ->withPivot(['status', 'created_at', 'updated_at'])
//            ->withTimestamps()
//            ->orderByPivot('created_at')
//            ->using(Deliverable::class);
//    }


    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function code(): MorphOne
    {
        return $this->morphOne(Code::class, 'codeable');
    }
}
