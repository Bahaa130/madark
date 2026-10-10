<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', [
            'callbackUrl' => $this->callbackUrl($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.credentialsError'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->callbackUrl($request));
    }

    /**
     * Only same-origin paths are honoured, so ?callbackUrl= cannot be used
     * as an open redirect.
     */
    private function callbackUrl(Request $request): string
    {
        $url = (string) $request->query('callbackUrl', '/dashboard');

        return str_starts_with($url, '/') && ! str_starts_with($url, '//')
            ? $url
            : '/dashboard';
    }
}