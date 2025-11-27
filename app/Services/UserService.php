<?php

namespace App\Services;
use App\Models\User;

class UserService
{
  public function create(array $data)
  {
    return User::create($data);
  }

}
