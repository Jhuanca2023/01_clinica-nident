<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUserRating extends Model
{
    use HasFactory;

    protected $table = 'product_user_ratings';

    protected $fillable = [
        'id_producto',
        'id_usuario',
        'rating',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
