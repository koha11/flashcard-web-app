<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'dob',
    ];

    protected $casts = [
        'dob' => 'date',
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
