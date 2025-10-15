<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Collection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['owner_id', 'name', 'tags', 'access_level', 'played_count', 'favorited_count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
