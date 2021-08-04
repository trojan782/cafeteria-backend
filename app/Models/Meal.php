<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'name',
        'qty',
        'price'
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at'
    ];
}
