<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Support\Facades\Log;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        Log::debug('Authenticate Middleware: handle method called.', ['path' => $request->path(), 'guard' => $guard]);
        if ($this->auth->guard($guard)->guest()) {
            Log::warning('Authenticate Middleware: User is guest, returning 401.');
            return response('Unauthorized.', 401);
        }

        $response = $next($request);
        if ($request->user()) {
            $response->headers->set('X-Token', $request->user()->getToken());
        }
        return $response;
    }
}
