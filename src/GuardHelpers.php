<?php

namespace Ckryo\Laravel\Admin;
use Ckryo\Laravel\Http\ErrorCodeException;
use Ckryo\Laravel\Http\Models\LogiAction;

/**
 * These methods are typically the same across all guards.
 */
trait GuardHelpers
{
    /**
     * The currently authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * Determine if the current user is authenticated.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function authenticate()
    {
        $token = $this->request->auth_token = $this->request->hasHeader('api-token') ? $this->request->header('api-token') : $this->request->get('api_token');
        if (!LogiAction::where('data', $token)->first()) {
            throw new ErrorCodeException(200, '需要登录');
        }

        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new ErrorCodeException(200);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }


    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->id;
        }
    }
}
