<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AccountService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

  protected AccountService $accountService;
  protected UserService $userService;

  public function __construct(AccountService $accountService, UserService $userService)
  {
    $this->accountService = $accountService;
    $this->userService = $userService;
  }

  public function me(Request $request)
  {
    $account = $request->user();
    return response()->json([
      'account' => $account,
      'user' => $account->user,
    ]);
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
      return response()->json(['message' => 'Invalid credentials'], 401);

    }
    /** @var \App\Models\Account $account */
    $account = Auth::user();
    // Tạo token để client lưu (localStorage / cookie)
    $token = $account->createToken('access_token')->plainTextToken;

    return response()->json([
      'account' => $account,
      'user' => $account->user,
      'token' => $token,
    ]);
  }

  public function signup(Request $request)
  {
    $data = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
      'name' => ['required', 'string', 'max:255'],
      'dob' => ['required', 'date'],
    ]);

    $user = $this->userService->create([
      'name' => $data['name'],
      'email' => $data['email'],
      'dob' => $data['dob'],
    ]);

    $data['id'] = $user->id;

    $account = $this->accountService->create($data);

    // Tạo token để client lưu (localStorage / cookie)
    $token = $account->createToken('access_token')->plainTextToken;

    return response()->json([
      'account' => $account,
      'user' => $account->user,
      'token' => $token,
    ]);
  }

  public function logout(Request $request)
  {
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out']);
  }
}

?>