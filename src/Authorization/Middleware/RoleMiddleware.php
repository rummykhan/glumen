<?php
namespace Glumen\Authorization\Middleware;
use Glumen\Authorization\Exceptions\UnauthorizedAccess;

class RoleMiddleware
{
    public function handle($request, \Closure $next, $role, $requireAll = false)
    {
        $roles = explode('|', $role);

        if (! app('authorization')->hasRole($roles, $requireAll)) {
            throw new UnauthorizedAccess();
        }
        return $next($request);
    }
}