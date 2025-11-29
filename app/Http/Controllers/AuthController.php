<?php
namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Services\AccountService;
use App\Services\UserService;
use App\Services\MailerService;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Str;

class AuthController extends Controller
{

  protected AccountService $accountService;
  protected UserService $userService;
  protected MailerService $mailer;

  public function __construct(AccountService $accountService, UserService $userService, MailerService $mailer)
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

    if (!$account->email_verified_at) {
      return response()->json([
        'message' => 'Please verify your email address before logging in.',
        'isEmailVerified' => false,
      ], 200);
    }
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

  public function forgotPassword(Request $request)
  {
    $data = $request->validate([
      'email' => ['required', 'email'],
    ]);

    $account = $this->accountService->findByEmail($data['email']);

    if (!$account) {
      return response([
        'message' => 'Your email address is not registered in our system.',
      ], 404);
    }

    // Just set new password for user directly for simplicity
    $newPassword = Str::random(12);

    $this->accountService->update($account, [
      'password' => $newPassword,
    ]);

    $this->mailer->sendResetPassword($account->user, $newPassword);

    return response()->json([
      'message' => 'If that email address is in our system, we have emailed you a password reset link.',
    ]);
  }

  public function changePassword(Request $request)
  {
    $data = $request->validate([
      'current_password' => ['required'],
      'new_password' => ['required', 'min:6'],
    ]);

    /** @var \App\Models\Account $account */
    $account = $request->user();

    if (!Hash::check($data['current_password'], $account->password)) {
      throw ValidationException::withMessages([
        'current_password' => ['Current password is incorrect.'],
      ]);
    }

    $this->accountService->update($account, [
      'password' => $data['new_password'],
    ]);

    return response()->json([
      'message' => 'Password changed successfully.',
    ]);
  }

  public function logout(Request $request)
  {
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out']);
  }
}

?>