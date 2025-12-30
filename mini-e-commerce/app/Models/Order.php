<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'orders'; // pastikan ini benar

    protected $fillable = [
        'order_code',
        'user_id',
        'total_price',
        'payment_status',
        'status',
    ];

    protected $casts = [
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
