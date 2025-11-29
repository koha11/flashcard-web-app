<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Services\AccountService;
use App\Services\MailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailVerificationController extends Controller
{
    protected AccountService $accountService;
    protected MailerService $mailer;


    public function __construct(AccountService $accountService, MailerService $mailer)
    {
        $this->accountService = $accountService;
        $this->mailer = $mailer;
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $record = EmailVerification::where('token', $data['token'])->first();

        if (!$record) {
            return response()->json([
                'message' => 'Verification link is invalid or already used.',
            ], 400);
        }

        if ($record->expires_at->isPast()) {
            return response()->json([
                'message' => 'Verification link has expired.',
            ], 400);
        }

        $user = $record->user;

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $account = $user->account;

        // Mark user as verified
        $account->email_verified_at = Carbon::now();
        $account->save();

        // Delete token
        $record->delete();

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    public function check(Request $request)
    {

        $data = $request->validate([
            'email' => ['required', 'string', "email"],
        ]);

        $account = $this->accountService->findByEmail($data['email']);

        $record = EmailVerification::whereHas('account', function ($query) use ($data) {
            $query->where('email', $data['email']);
        })->first();

        if (!$record) {
            return response()->json([
                'message' => 'No pending verification found for this email.',
            ], 404);
        }

        if ($record->expires_at->isPast()) {
            $record->delete();

            $newRecord = EmailVerification::create([
                'user_id' => $account->user->id,
                'token' => \Illuminate\Support\Str::random(64),
                'expires_at' => Carbon::now()->addDay(),
            ]);

            $this->mailer->sendVerificationLink($account->user, $newRecord->token);
        } else {
            $this->mailer->sendVerificationLink($account->user, $record->token);
        }

        return response()->json([
            'message' => 'Verification email resent if there was a pending verification.',
        ]);
    }
}
