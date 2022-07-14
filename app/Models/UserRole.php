<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    /** Declare Table */
    protected $table = 'user_roles';

    /** Set attribute for can filled */
    protected $fillable = [
        'user_id',
        'name'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**Set relation to User Models */
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
