<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailVerificationController extends Controller
{
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
}
