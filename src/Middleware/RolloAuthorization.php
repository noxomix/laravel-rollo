<?php

namespace Noxomix\LaravelRollo\Middleware;

use Closure;
use Illuminate\Http\Request;
use Noxomix\LaravelRollo\Exceptions\RolloAuthorizationException;

class RolloAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $action
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $action = 'manage')
    {
        if (!config('rollo.authorization.enabled', true)) {
            return $next($request);
        }

        if (!$request->user()) {
            throw new RolloAuthorizationException('Authentication required.');
        }

        if (!$request->user()->can("rollo.{$action}")) {
            throw new RolloAuthorizationException("Unauthorized to perform Rollo action: {$action}");
        }

        return $next($request);
    }
}