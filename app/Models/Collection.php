<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tags',
        'description',
        'owner_id',
        'access_level',
        'played_count',
        'favorited_count',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function flashcards()
    {
        return $this->belongsToMany(Flashcard::class, 'collection_flashcard')
            ->withTimestamps();
    }

    public function accessUsers()
    {
        return $this->belongsToMany(User::class, 'collection_access_users');
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorited_collections')
            ->withPivot('favorited_date');
    }

    public function recents()
    {
        return $this->belongsToMany(User::class, 'recent_collections')
            ->withPivot('viewed_date');
    }
}
