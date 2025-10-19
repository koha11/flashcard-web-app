<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flashcard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'front_side',
        'back_side',
        'tags',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_flashcard')
            ->withTimestamps();
    }

    public function reports()
    {
        return $this->hasMany(ReportedFlashcard::class);
    }
}
