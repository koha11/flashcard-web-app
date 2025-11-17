<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flashcard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'term',
        'definition',
    ];

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_flashcard')
            ->withTimestamps();
    }
}
