<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Favorite;

class User extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'postal_code',
        'address',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //出品した商品
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    //コメントした履歴
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    //いいねした商品一覧
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    //購入した注文一覧
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

}
