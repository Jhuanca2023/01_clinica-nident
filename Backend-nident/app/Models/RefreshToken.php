<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RefreshToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public static function generateToken($userId, $expiresInDays = 30)
    {
        $token = Str::uuid()->toString();
        $expiresAt = Carbon::now()->addDays($expiresInDays);

        return self::create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid()
    {
        return $this->expires_at->isFuture();
    }
}