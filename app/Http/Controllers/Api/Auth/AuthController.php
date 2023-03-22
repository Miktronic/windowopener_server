<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use App\Mail\ForgotPassword;
use App\Mail\ResetPassword;
use App\Models\User;
use App\Models\UserLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function authenticate(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt(['email' => $request['email'], 'password' => $request['password']], $request['remember'])) {
            // $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role != 'admin') {
                return response()->json(['msg' => 'Permission denied'], 403);
            }
            $user = User::findOrFail($user->id);
            $token = $user->createToken('authToken')->plainTextToken;

            UserLog::create(['user_id' => $user->id, 'ip' => $request->ip()]);

            return response()->json(['data' => $user, 'token' => $token, 'token_type' => 'Bearer']);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function authenticateAppUser(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'The credentials are invalid. Please try again.'
            ], 400);
        }

        UserLog::create(['user_id' => Auth::user()->id, 'ip' => $request->ip()]);
        $user = User::with('settings')->where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data'=> $user,
        ]);
    }

    public function registerAppUser(Request $request): JsonResponse
    {
        $post_data = $request->validate([
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => explode("@", $post_data['email'])[0],
            'email' => $post_data['email'],
            'password' => Hash::make($post_data['password']),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        $otp = $this->createPasswordResetRecode($request['email']);
        Mail::to($request['email'])->send(new EmailVerification($otp));

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    private function createPasswordResetRecode($email): int
    {
        $six_digit_random_number = rand(100000, 999999);
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $six_digit_random_number,
            'created_at' => date('Y-m-d H:m:s')
        ]);
        return $six_digit_random_number;
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        $request->session()->regenerate();
        return response()->json(['data' => $user], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(["message" => "User successfully logged out"], 204);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user()->id);
        // change password
        if (Hash::check($request->oldPassword, $user->password)) {
            $user->fill([
                'password' => Hash::make($request->newPassword)
            ])->save();
        } else {
            return response()->json(["message" => "Old password is wrong"], 403);
        }
        // logout
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(["message" => "User successfully logged out"], 204);
    }

    public function changePasswordByAppUser(Request $request): JsonResponse
    {
        $request->validate([
            'oldPassword' => ['required', 'string', 'min:8'],
            'newPassword' => ['required', 'string', 'min:8'],
        ]);

        $user = User::findOrFail($request->user()->id);
        // change password
        if (Hash::check($request->oldPassword, $user->password)) {
            $user->fill([
                'password' => Hash::make($request->newPassword)
            ])->save();
        } else {
            return response()->json(["message" => "Old password is wrong"], 403);
        }
        // logout
        $request->session()->regenerateToken();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = User::find($request->user()->id);
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('gps_location')) {
            $user->gps_location = $request->gps_location;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        if ($request->has('zip_code')) {
            $user->zip_code = $request->zip_code;
        }
        if ($request->has('latitude')) {
            $user->latitude = $request->latitude;
        }
        if ($request->has('longitude')) {
            $user->longitude = $request->longitude;
        }
        $user->save();
        return response()->json([
            'data' => $user->makeHidden([
                'created_at', 'updated_at', 'email_verified_at', 'role'
            ])
        ], 201);
    }

    public function close(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        User::destroy($request->user()->id);
        return response()->json(["message" => "Account successfully closed"], 204);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ]);

        $six_digit_random_number = random_int(100000, 999999);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $six_digit_random_number,
            'created_at' => date('Y-m-d H:m:s')
        ]);

        Mail::to($request->email)->send(new ForgotPassword($six_digit_random_number));

        if (Mail::failures()) {
            return response()->json(["message" => "Sorry! Please try again later"], 424);
        }

        return response()->json(["message" => "Great! Successfully send in your mail"], 200);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'otp' => ['required', 'string', 'min:6', 'max:6']
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->where('token', $request->otp)->first();
        if (!$record) {
            return response()->json(["message" => "Your OTP code is invalid."], 403);
        }
        if (strtotime($record->created_at) - strtotime(date('Y-m-d H:i:s')) > 5 * 60) {
            return response()->json(["message" => "Your OTP code was expired. Please try again later."], 403);
        }

        $user = User::where('email', $request->email)->first();
        $password = Str::random(10);
        $user->fill([
            'password' => Hash::make($password)
        ])->save();

        Mail::to($request->email)->send(new ResetPassword($password));

        if (Mail::failures()) {
            return response()->json(["message" => "Sorry! Please try again later"], 424);
        }

        return response()->json(["message" => "Great! Successfully send in your email."], 200);
    }

    public function emailVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'otp' => ['required', 'string', 'min:6', 'max:6']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->fails());
        }
        try {
            $data = $validator->validated();
            $record = DB::table('password_resets')->where('email', $data['email'])->where('token',
                $data['otp'])->first();
            if (!$record) {
                return response()->json(["message" => "Your OTP code is invalid."], 403);
            }
            if (strtotime($record->created_at) - strtotime(date('Y-m-d H:i:s')) > 5 * 60) {
                $record->delete();
                return response()->json(["message" => "Your OTP code was expired. Please try again later."], 403);
            }

            User::where('email', $data['email'])->update([
                'email_verified_at' => now()
            ]);

            $record->delete();

            return response()->json(["message" => "Great! Successfully email verified."], 200);
        } catch (Exception $ex) {
            return response()->json(["message" => $ex->getMessage()], 424);
        }
    }

    public function resendEmailVerificationCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $data = $validator->validated();
            $otp = $this->createPasswordResetRecode($data['email']);
            Mail::to($data['email'])->send(new EmailVerification($otp));
            if (Mail::failures()) {
                return response()->json(["message" => "Sorry! Please try again later"], 424);
            }
            return response()->json(["message" => "Great! Successfully send in your email"], 200);
        } catch (Exception $ex) {
            return response()->json(["message" => $ex->getMessage()], 424);
        }
    }
}