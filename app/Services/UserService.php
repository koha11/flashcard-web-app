<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
  public function create(array $data)
  {
    return User::create($data);
  }

}
