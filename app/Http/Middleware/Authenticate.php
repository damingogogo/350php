<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->is('backend') || $request->is('admin/backend') || $request->is('teacher/backend') || $request->is('student/backend')) {
            return route('platform.backend.login');
        }

        return route('login');
    }
}
