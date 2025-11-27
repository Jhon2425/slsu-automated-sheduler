<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacultyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isFaculty()) {
            abort(403, 'Unauthorized access. Faculty privileges required.');
        }

        return $next($request);
    }
}
