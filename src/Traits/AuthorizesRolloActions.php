<?php

namespace Noxomix\LaravelRollo\Traits;

use Illuminate\Support\Facades\Gate;
use Noxomix\LaravelRollo\Exceptions\RolloAuthorizationException;

trait AuthorizesRolloActions
{
    /**
     * Check if the current user is authorized to perform a Rollo action.
     *
     * @param string $action
     * @param mixed ...$arguments
     * @throws RolloAuthorizationException
     * @return void
     */
    protected function authorizeRolloAction(string $action, ...$arguments): void
    {
        if (!config('rollo.authorization.enabled', true)) {
            return;
        }

        $user = auth()->user();
        
        if (!$user) {
            throw new RolloAuthorizationException('Authentication required to perform this action.');
        }

        if (!Gate::forUser($user)->allows("rollo.{$action}", $arguments)) {
            throw new RolloAuthorizationException("Unauthorized to perform action: {$action}");
        }
    }

    /**
     * Get the authenticated user for authorization.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function getAuthUser()
    {
        return auth()->user();
    }
}