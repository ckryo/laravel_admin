<?php

namespace Ckryo\Laravel\Admin\Middleware;

use Ckryo\Laravel\Admin\Auth;
use Closure;

class AuthenticateMiddleware
{
    /**
     * The authentication factory instance.
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
     * @param  string[]  ...$roles
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (empty($roles)) {
            $this->auth->authenticate();
        } else {
            dd($roles);
        }

        return $next($request);
    }
}
