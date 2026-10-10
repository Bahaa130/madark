<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\Role;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $allowed = array_map(
            fn (string $r) => constant(Role::class . '::' . strtoupper($r))->value,
            $roles
        );

        if (! in_array($user->role->value, $allowed, true)) {
            return redirect($this->homeFor($user->role));
        }

        return $next($request);
    }

    private function homeFor(Role $role): string
    {
        return match ($role) {
            Role::ADMIN => route('admin.dashboard'),
            Role::INSTRUCTOR => route('instructor.dashboard'),
            default => route('student.orbit'),
        };
    }
}