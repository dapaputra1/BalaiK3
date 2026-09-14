<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'service_parameter_id',
        'qty',
        'price',
        'package_key',
        'package_name',
        'package_price',
        'package_details',
    ];

    protected $casts = [
        'package_details' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function serviceParameter()
    {
        return $this->belongsTo(ServiceParameter::class);
    }
}
