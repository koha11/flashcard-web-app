<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\EmailVerification;
use App\Services\AccountService;
use App\Services\UserService;
use App\Services\VerificationMailerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Str;

class AuthController extends Controller
{

  protected AccountService $accountService;
  protected UserService $userService;

  protected VerificationMailerService $mailer;

  public function __construct(AccountService $accountService, UserService $userService, VerificationMailerService $mailer)
  {
    $this->accountService = $accountService;
    $this->userService = $userService;
    $this->mailer = $mailer;
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

    $user->account()->create([
      'email' => $data['email'],
      'password' => bcrypt($data['password']),
    ]);

    $token = Str::random(64);

    EmailVerification::create([
      'user_id' => $user->id,
      'token' => $token,
      'expires_at' => Carbon::now()->addDay(), // 24h
    ]);

    $sent = $this->mailer->sendVerificationLink($user, $token);

    if (!$sent) {
      throw ValidationException::withMessages([
        'email' => ['Cannot send verification email. Please try again later.'],
      ]);
    }

    return response()->json([
      'message' => 'Registered successfully. Please check your email to verify your account.',
    ], 201);
  }

  public function logout(Request $request)
  {
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out']);
  }
}

?>