<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Support\Facades\Log;

class AuthAPI extends Controller
{
    private function initResponse($user) {
        if (!$user) return response()->json(['errors' => ['ログインできません']], 401);

        $data = [
            'user' => $user,
            'timeout' => env('APP_AUTH_TIMEOUT', 30),
        ];

        Log::debug('AuthAPI@initResponse: Response data:', $data);
        return response()->json($data)->withHeaders(['X-Token' => $user->getToken()]);
    }

    /**
     * Login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function login(Request $request)
    {
        Log::info('AuthAPI@login: Request received.');
        $user = null;
        try {
            if ($request->filled('email') && $request->filled('password')) {
                Log::info('AuthAPI@login: Email and password are filled. Attempting User::LoginByPassword.');
                $user = User::LoginByPassword(
                    $request->input('email'),
                    $request->input('password')
                );
                if ($user) {
                    Log::info('AuthAPI@login: User::LoginByPassword returned a user. Setting user in auth guard.');
                    app('auth')->setUser($user);
                } else {
                    Log::warning('AuthAPI@login: User::LoginByPassword returned null.');
                }
            } else {
                Log::warning('AuthAPI@login: Email or password not filled.');
            }
            Log::info('AuthAPI@login: Calling initResponse.');
            return $this->initResponse($user);
        } catch (\Exception $e) {
            Log::error('AuthAPI@login: An error occurred during login process. Error: ' . $e->getMessage());
            return response()->json(['errors' => ['ログイン処理中にエラーが発生しました。']], 500);
        }
    }

    /**
     * Init
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function init(Request $request)
    {
        return $this->initResponse($request->user());
    }

    /**
     * Change Password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function password(Request $request)
    {
        $user = $request->user();
        if (!Hash::check($request->input('password'), $user->password)) return ['errors' => ['パスワードが間違っています。']];
        // $password = sha1($request->input('password'));
        // if ($user->password != $password) return ['errors' => ['パスワードが間違っています。']];
        if (empty($request->input('newpass'))) return ['errors' => ['新パスワードが必須です。']];
        if ($request->input('password') == $request->input('newpass')) return ['errors' => ['パスワードが変更されていません。']];
        
        $user->password = $request->input('newpass');
        $user->save();
        return ['success' => true];
    }

    public function fake(Request $request, $as = false)
    {
        $user = $request->user();
        if ($user->id == 1 && !empty($as)) {
            $user = $user->fakeAs($as);
        } else if ($user->fake && $user->fake->id == 1 && !$as) {
            $user = $user->fake;
        }
        
        app('auth')->setUser($user);
        return $this->initResponse($user);
    }
}
