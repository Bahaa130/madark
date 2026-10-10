<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * The authenticated user.
     *
     * Routes are guarded by the `auth` middleware, so this never returns null
     * in practice; the throw keeps static analysis honest.
     */
    protected function user(): User
    {
        $user = auth()->user();

        abort_if($user === null, 401);

        return $user;
    }
}