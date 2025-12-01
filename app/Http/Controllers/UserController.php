<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{

  private UserService $service;

  public function __construct(UserService $service)
  {
    $this->service = $service;
  }

  public function getByEmail(Request $request)
  {
    $email = $request->query('email');
    $userId = $request->user()->user->id;
    return $this->service->getByEmail($email, $userId);
  }

  public function editUserInfo(Request $request)
  {
    $data = $request->validate([
      'name' => ['required', 'string', 'max:100'],
      'dob' => ['required', 'date', 'max:500'],
    ]);

    $userId = $request->user()->user->id;

    return response()->json($this->service->editUserInfo($userId, $data));
  }

}


?>