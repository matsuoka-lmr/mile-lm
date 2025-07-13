<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Cookie;

class AuthAPI extends Controller
{
    private function initResponse($user) {
        if (!$user) return response()->json(['errors' => ['ログインできません']], 401);

        $data = [
            'user' => $user,
            'timeout' => env('APP_AUTH_TIMEOUT', 30),
        ];

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
        $user = null;
        if ($request->filled('email') && $request->filled('password')) {
            $credentials = $request->only(['email', 'password']);
            $user = User::LoginByPassword(
                $request->input('email'),
                $request->input('password')
            );
            if ($user) app('auth')->setUser($user);
        }
        return $this->initResponse($user);
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
