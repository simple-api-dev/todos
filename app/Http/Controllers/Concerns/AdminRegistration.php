<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PendingRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Registering with the developer's own email address (the address the apikey belongs to) makes the account an admin,
 * so nothing is created until whoever holds that mailbox enters a code emailed to it. Otherwise anyone who knew the
 * address could register it first, either to squat it or to become admin.
 */
trait AdminRegistration
{
    private function isDeveloperEmail($email) : bool
    {
        $developer = strtolower(trim((string)$this->integration_email));
        return $developer !== '' && strtolower(trim((string)$email)) === $developer;
    }

    /**
     * Keep the registration (password already hashed) until the code is entered, and email the code.
     */
    private function startAdminRegistration(Request $request) : JsonResponse
    {
        $email = strtolower(trim($request->email));

        $pending = PendingRegistration::where([['integration_id', $this->integration_id], ['email', $email]])->first();
        if ($pending && $pending->sent_at && $pending->sent_at->diffInSeconds(Carbon::now()) < 60) {
            return response()->json(['message' => "A code was sent a moment ago.  Please wait a minute before asking for another."], 429);
        }

        $code = str_pad((string)random_int(0, 999999), 6, "0", STR_PAD_LEFT);
        PendingRegistration::updateOrCreate(
            ['integration_id' => $this->integration_id, 'email' => $email],
            [
                'name' => trim($request->name),
                'password_hash' => Hash::make($request->password),
                'code_hash' => Hash::make($code),
                'expires_at' => Carbon::now()->addMinutes(15),
                'attempts' => 0,
                'sent_at' => Carbon::now(),
            ]
        );

        try {
            Mail::send('confirm', ["name" => trim($request->name), "code" => $code, "minutes" => 15], function ($message) use ($email, $request) {
                $message->to($email, trim($request->name))->subject('Confirm your email address');
            });
        } catch (\Exception $ex) {
            error_log("admin registration mail failed: " . $ex->getMessage());
            return response()->json(['message' => "We could not send the email.  Please try again shortly."], 500);
        }

        return response()->json([
            'message' => "This is the email address your apikey belongs to, so it becomes the admin account.  We sent a 6 digit code to it.  Send it to POST /api/users/register/confirm with the email to finish registering.",
            'confirmation_required' => true,
        ], 202);
    }

    /**
     * The pending registration for the email if the code is right (it is then used up), otherwise null.
     */
    private function takePendingRegistration(string $email, string $code) : ?PendingRegistration
    {
        $pending = PendingRegistration::where([['integration_id', $this->integration_id], ['email', strtolower(trim($email))]])->first();

        if (!$pending || !$pending->expires_at || $pending->expires_at->isPast() || $pending->attempts >= 5) {
            return null;
        }
        if (!Hash::check($code, $pending->code_hash)) {
            $pending->attempts = $pending->attempts + 1;
            $pending->save();
            return null;
        }

        $pending->delete();
        return $pending;
    }
}
