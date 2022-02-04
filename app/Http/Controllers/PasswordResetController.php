<?php
namespace App\Http\Controllers;

use App\Mail\Password_Reset_Request;
use App\Mail\Password_Reset_Success;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends BaseController
{
    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->statut) {
            return response()->json(['status' => false,
                'message' => 'We can\'t find a user with that e-mail address.',
            ]);
        }


        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60),
            ]
        );
        if ($user && $passwordReset) {
            $this->Set_config_mail();
        }
        // Set_config_mail => BaseController
        $url = url('/password/find/' . $passwordReset->token);
        Mail::to($user->email)->send(new Password_Reset_Request($passwordReset->token, $url));

        return response()->json(['status' => true,
            'message' => 'We have e-mailed your password reset link!',
        ], 200);
    }
    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        if (!$passwordReset) {
            return response()->json([
                'message' => 'This password reset token is invalid.',
                'success' => false,
            ]);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(60)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.',
                'success' => false,
            ]);
        }

        return view('auth.passwords.reset', ["token" => $token]);
    }
    /**
     * Reset password

     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'We can\'t find a user with that e-mail address.',
                'status' => false,
                'code' => 2,
            ]);
        }

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email],
        ])->first();
        if (!$passwordReset) {
            return response()->json([
                'message' => 'This password reset token is invalid.',
                'status' => false,
                'code' => 3,
            ]);
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $this->Set_config_mail(); // Set_config_mail => BaseController
        Mail::to($request->email)->send(new Password_Reset_Success());

        return response()->json([
            'user' => $user,
            'message' => 'Your Password has been changed.',
            'status' => true,
            'code' => 1,
        ]);
    }
}
