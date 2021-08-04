<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodHistory extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'user_id',
        'amount',
        'items',
        'active',
        'barcode'
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
