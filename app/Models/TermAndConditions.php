<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermAndConditions extends Model
{
    use HasFactory;

    /**Initialize Tables */
    protected $table = 'term_and_conditions';


    /**Declare attribute can be filled */
    protected $fillable =
    [
        'title',
        'content',
        'type',
        'image'
    ];

    /** Declaring hidden attributes when showing in json */
    protected $hidden =
    [
        'created_at',
        'updated_at'
    ];
}
