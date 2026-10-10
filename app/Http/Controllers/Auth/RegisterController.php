<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', [
            'callbackUrl' => $this->callbackUrl($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'string', 'in:student,instructor'],
        ]);

        $role = $data['role'] === 'instructor' ? Role::INSTRUCTOR : Role::STUDENT;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($this->callbackUrl($request));
    }

    private function callbackUrl(Request $request): string
    {
        $url = (string) $request->query('callbackUrl', '/dashboard');

        return str_starts_with($url, '/') && ! str_starts_with($url, '//')
            ? $url
            : '/dashboard';
    }
}
