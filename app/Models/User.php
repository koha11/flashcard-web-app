<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'password' => 'hashed',
    ];

  
    public function ownedCollections()
    {
        return $this->hasMany(Collection::class, 'owner_id');
    }
    public function accessibleCollections()
    {
        return $this->belongsToMany(Collection::class, 'collection_access_users', 'user_id', 'collection_id')
            ->withPivot('can_edit');
    }
    public function favoritedCollections()
    {
        return $this->belongsToMany(Collection::class, 'favorited_collections', 'user_id', 'collection_id')
            ->withPivot('favorited_date');
    }
    public function recentCollections()
    {
        return $this->belongsToMany(Collection::class, 'recent_collections', 'user_id', 'collection_id')
            ->withPivot('viewed_date');
    }

}
