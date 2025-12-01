<?php

namespace App\Services;
use App\Models\User;

class UserService
{
  public function create(array $data)
  {
    return User::create($data);
  }

  public function getByEmail($email, $userId)
  {
    if (!empty($email)) {
      return User::where('email', 'like', "%{$email}%")->where('id', '!=', $userId)->get();
    }
    return [];
  }

  public function editUserInfo($userId, $data)
  {
    $user = User::findOrFail($userId);
    $user->update($data);
    return $user;
  }

}
