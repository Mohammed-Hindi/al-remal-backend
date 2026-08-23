<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowedRoles = array_map(
            fn(string $role) => UserRole::from($role),
            $roles
        );

        if (! $user || ! in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية للوصول لهذا المورد',
            ], 403);
        }

        return $next($request);
    }
}
