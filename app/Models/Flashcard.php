<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'front_side',
        'back_side',
        'tags',
    ];

    // Accessor/Mutator for tags as array <-> CSV string
    protected function tagsArray(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->tags
            ? array_values(array_filter(array_map('trim', explode(',', (string) $this->tags))))
            : [],
            set: fn($value) => is_array($value) ? implode(',', $value) : $value
        );
    }

    /** Relationships */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function collections()
    // {
    //     return $this->belongsToMany(Collection::class, 'collection_flashcard', 'flashcard_id', 'collection_id');
    // }

    /** Scopes */
    // public function scopeNotDeleted($q)
    // {
    //     return $q->where('deleted', false);
    // }
}
