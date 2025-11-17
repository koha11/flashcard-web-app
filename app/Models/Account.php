<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $table = 'accounts';

  protected $fillable = [
    'email',
    'email_verified_at',
    'password',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
  ];


  public function user()
  {
    return $this->hasOne(User::class, 'id', 'id'); 
  }
}
