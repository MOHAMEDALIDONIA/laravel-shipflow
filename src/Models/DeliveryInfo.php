<?php

namespace Mohamedali\LaravelShipping\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryInfo extends Model
{
    protected $table = 'delivery_info';

    protected $fillable = [
        'model_id',
        'model_type',
        'provider',
        'status',
        'info',
    ];

    protected $casts = [
        'info' => 'array'
    ];
}